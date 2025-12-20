import { Router } from "express";
import { requireUser } from "../middleware/auth";
import { prisma } from "../db";
import { config } from "../config";
import { startOfDay, endOfDay } from "../utils/tokens";
import { NotificationService } from "../services/NotificationService";
import { addFlash } from "../middleware/flash";

const router = Router();
router.use(requireUser);

router.get("/", async (req, res, next) => {
  try {
    const user = req.currentUser;
    if (!user) {
      req.session.destroy(() => res.redirect("/login"));
      return;
    }
    if (user.status === "blocked" || user.status === "rejected") {
      req.session.destroy(() => res.redirect("/"));
      return;
    }

    const tier = user.status === "approved" ? "full" : "demo";
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

    const conversation = await prisma.conversation.findFirst({
      where: { userId: user.id },
      orderBy: { startedAt: "desc" },
      include: { messages: { orderBy: { createdAt: "asc" } } }
    });

    const lastMessageAt =
      conversation && conversation.messages.length
        ? conversation.messages[conversation.messages.length - 1].createdAt
        : null;

    res.render("app", {
      title: "Chat",
      tier,
      user,
      remaining: Math.max(dailyLimit - usedCount, 0),
      messages: conversation?.messages || [],
      conversationId: conversation?.id || null,
      lastMessageAt,
      status: user.status,
      csrfToken: req.csrfToken()
    });
  } catch (err) {
    next(err);
  }
});

router.post("/request", async (req, res, next) => {
  try {
    const user = req.currentUser!;
    const type = ["live_chat", "meeting"].includes(req.body.type)
      ? req.body.type
      : "meeting";
    const message = req.body.message || "";

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
    addFlash(req, "success", "Request sent. We will get back to you soon.");
    return res.redirect("/app");
  } catch (err) {
    return next(err);
  }
});

export default router;
