import { Router } from "express";
import { requireUser } from "../middleware/auth";
import { prisma } from "../db";
import { config } from "../config";
import { ChatService, ChatTier } from "../services/ChatService";
import { endOfDay, startOfDay } from "../utils/tokens";
import { addFlash } from "../middleware/flash";
import { NotificationService } from "../services/NotificationService";
import { rateLimitByIp } from "../middleware/rateLimit";

const router = Router();

router.post("/chat", requireUser, async (req, res, next) => {
  try {
    const user = req.currentUser;
    if (!user) {
      req.session.destroy(() => res.status(401).json({ error: "Not authenticated" }));
      return;
    }
    if (user.status === "blocked") {
      return res.status(403).json({ error: "Account blocked" });
    }

    const tier: ChatTier = user.status === "approved" ? "full" : "demo";
    const dailyLimit = tier === "full" ? config.fullLimitPerDay : config.demoLimitPerDay;
    const todayStart = startOfDay();
    const todayEnd = endOfDay();

    const usedCount = await prisma.message.count({
      where: {
        sender: "user",
        conversation: { userId: user.id },
        createdAt: { gte: todayStart, lte: todayEnd }
      }
    });

    if (usedCount >= dailyLimit) {
      return res.status(429).json({ error: "Daily limit reached", remaining: 0 });
    }

    const content = (req.body?.message || "").toString().trim();
    if (!content) {
      return res.status(400).json({ error: "Message is required" });
    }

    let conversationId = req.session.conversationId;
    let conversation =
      conversationId &&
      (await prisma.conversation.findFirst({
        where: { id: conversationId, userId: user.id }
      }));

    if (!conversation || conversation.status === "closed") {
      conversation = await prisma.conversation.create({
        data: {
          userId: user.id,
          tierAtTime: tier,
          status: "open",
          ipAddress: req.ip,
          userAgent: req.headers["user-agent"] || "",
          lastActivityAt: new Date()
        }
      });
      req.session.conversationId = conversation.id;
    }

    await prisma.message.create({
      data: {
        conversationId: conversation.id,
        sender: "user",
        content
      }
    });
    await prisma.conversation.update({
      where: { id: conversation.id },
      data: { lastActivityAt: new Date(), status: "open" }
    });

    const history = await prisma.message.findMany({
      where: { conversationId: conversation.id },
      orderBy: { createdAt: "asc" },
      take: 20
    });

    const replyText = await ChatService.generateReply(
      history.map((m) => ({ sender: m.sender as "user" | "assistant", content: m.content })),
      tier
    );

    await prisma.message.create({
      data: {
        conversationId: conversation.id,
        sender: "assistant",
        content: replyText
      }
    });
    await prisma.conversation.update({
      where: { id: conversation.id },
      data: { lastActivityAt: new Date() }
    });

    const remaining = Math.max(dailyLimit - (usedCount + 1), 0);

    return res.json({ reply: replyText, remaining, tier, conversationId: conversation.id });
  } catch (err) {
    return next(err);
  }
});

router.get(
  "/conversations/:id/messages",
  rateLimitByIp("poll", 60, 5 * 60 * 1000),
  async (req, res, next) => {
    try {
      if (!req.session.userId && !req.session.isAdmin) {
        return res.status(401).json({ error: "Unauthorized" });
      }
      const convo = await prisma.conversation.findUnique({
        where: { id: req.params.id },
        include: { user: true }
      });
      if (!convo) return res.status(404).json({ error: "Not found" });

      const isAdmin = !!req.session.isAdmin;
      if (!isAdmin && convo.userId !== req.session.userId) {
        return res.status(403).json({ error: "Forbidden" });
      }

      const after = req.query.after ? new Date(req.query.after.toString()) : null;
      const messages = await prisma.message.findMany({
        where: {
          conversationId: convo.id,
          ...(after ? { createdAt: { gt: after } } : {})
        },
        orderBy: { createdAt: "asc" }
      });
      return res.json({ messages });
    } catch (err) {
      return next(err);
    }
  }
);

router.post("/handoff", requireUser, async (req, res, next) => {
  try {
    const user = req.currentUser;
    if (!user) {
      return res.status(401).json({ error: "Not authenticated" });
    }
    const type = req.body.type === "live_chat" ? "live_chat" : "meeting";
    const message = (req.body.message || "").toString().trim();
    if (!message) {
      return res.status(400).json({ error: "Message is required" });
    }

    let conversation =
      req.session.conversationId &&
      (await prisma.conversation.findFirst({
        where: { id: req.session.conversationId, userId: user.id }
      }));

    if (!conversation || conversation.status === "closed") {
      conversation = await prisma.conversation.create({
        data: {
          userId: user.id,
          tierAtTime: user.status === "approved" ? "full" : "demo",
          status: "open",
          ipAddress: req.ip,
          userAgent: req.headers["user-agent"] || "",
          lastActivityAt: new Date()
        }
      });
      req.session.conversationId = conversation.id;
    }

    const request = await prisma.handoffRequest.create({
      data: {
        userId: user.id,
        conversationId: conversation.id,
        type,
        message,
        status: "new"
      }
    });

    await prisma.auditLog.create({
      data: {
        actorType: "user",
        actorId: user.id,
        action: "handoff_request",
        metadata: { type, message },
        ipAddress: req.ip
      }
    });

    await NotificationService.notifyOwnerHandoffRequest(request, user);
    addFlash(req, "success", "Tony has been notified. You can keep this tab open.");
    return res.json({ ok: true, message: "Tony has been notified. You can keep this tab open.", conversationId: conversation.id });
  } catch (err) {
    return next(err);
  }
});

export default router;
