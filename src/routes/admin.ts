import { Router } from "express";
import { prisma } from "../db";
import { config } from "../config";
import { addFlash } from "../middleware/flash";
import { requireAdmin } from "../middleware/auth";

const router = Router();

router.get("/login", (req, res) => {
  res.render("admin/login", { title: "Admin Login", csrfToken: res.locals.csrfToken });
});

router.post("/login", async (req, res) => {
  const { password } = req.body;
  if (config.adminPassword && password === config.adminPassword) {
    req.session.isAdmin = true;
    addFlash(req, "success", "Admin signed in");
    return res.redirect("/admin");
  }
  addFlash(req, "error", "Invalid password");
  return res.redirect("/admin/login");
});

router.use(requireAdmin);

router.get("/", async (req, res, next) => {
  try {
    const pendingUsers = await prisma.user.findMany({
      where: { status: "pending" },
      orderBy: { createdAt: "asc" }
    });

    const recentRequests = await prisma.handoffRequest.findMany({
      orderBy: { createdAt: "desc" },
      take: 10,
      include: { user: true, conversation: true }
    });

    res.render("admin/dashboard", {
      title: "Admin",
      pendingUsers,
      recentRequests,
      csrfToken: res.locals.csrfToken
    });
  } catch (err) {
    next(err);
  }
});

router.get("/users/:id", async (req, res, next) => {
  try {
    const user = await prisma.user.findUnique({
      where: { id: req.params.id },
      include: {
        conversations: {
          orderBy: { startedAt: "desc" },
          include: { messages: { orderBy: { createdAt: "asc" } } }
        },
        handoffRequests: true
      }
    });

    if (!user) {
      addFlash(req, "error", "User not found");
      return res.redirect("/admin");
    }

    res.render("admin/user", {
      title: `User ${user.email}`,
      user,
      csrfToken: res.locals.csrfToken
    });
  } catch (err) {
    next(err);
  }
});

router.get("/chat/:conversationId", async (req, res, next) => {
  try {
    const conversation = await prisma.conversation.findUnique({
      where: { id: req.params.conversationId },
      include: {
        user: true,
        messages: { orderBy: { createdAt: "asc" } },
        handoffRequests: true
      }
    });

    if (!conversation) {
      addFlash(req, "error", "Conversation not found");
      return res.redirect("/admin");
    }

    res.render("admin/chat", {
      title: "Admin Chat",
      conversation,
      user: conversation.user,
      csrfToken: res.locals.csrfToken
    });
  } catch (err) {
    next(err);
  }
});

router.post("/chat/:conversationId/reply", async (req, res, next) => {
  try {
    const conversation = await prisma.conversation.findUnique({
      where: { id: req.params.conversationId }
    });
    if (!conversation) {
      addFlash(req, "error", "Conversation not found");
      return res.redirect("/admin");
    }

    const content = (req.body.message || "").toString().trim();
    if (!content) {
      addFlash(req, "error", "Message required");
      return res.redirect(`/admin/chat/${conversation.id}`);
    }

    await prisma.message.create({
      data: {
        conversationId: conversation.id,
        sender: "assistant",
        content,
        isOwnerReply: true
      }
    });
    await prisma.conversation.update({
      where: { id: conversation.id },
      data: { lastActivityAt: new Date(), status: "open" }
    });
    await prisma.auditLog.create({
      data: {
        actorType: "admin",
        actorId: req.sessionID,
        action: "admin_reply",
        metadata: { conversationId: conversation.id },
        ipAddress: req.ip
      }
    });

    addFlash(req, "success", "Reply sent");
    return res.redirect(`/admin/chat/${conversation.id}`);
  } catch (err) {
    next(err);
  }
});

router.post("/chat/:conversationId/status", async (req, res, next) => {
  try {
    const action = req.body.action;
    const conversation = await prisma.conversation.findUnique({
      where: { id: req.params.conversationId },
      include: { handoffRequests: true }
    });
    if (!conversation) {
      addFlash(req, "error", "Conversation not found");
      return res.redirect("/admin");
    }

    if (action === "acknowledge") {
      await prisma.handoffRequest.updateMany({
        where: { conversationId: conversation.id },
        data: { status: "acknowledged" }
      });
      addFlash(req, "success", "Marked acknowledged");
    } else if (action === "close") {
      await prisma.conversation.update({
        where: { id: conversation.id },
        data: { status: "closed", endedAt: new Date(), lastActivityAt: new Date() }
      });
      await prisma.handoffRequest.updateMany({
        where: { conversationId: conversation.id },
        data: { status: "closed" }
      });
      addFlash(req, "success", "Conversation closed");
    } else {
      addFlash(req, "error", "Invalid action");
    }

    await prisma.auditLog.create({
      data: {
        actorType: "admin",
        actorId: req.sessionID,
        action: `conversation_${action}`,
        metadata: { conversationId: conversation.id },
        ipAddress: req.ip
      }
    });

    return res.redirect(`/admin/chat/${conversation.id}`);
  } catch (err) {
    next(err);
  }
});

router.post("/users/:id/status", async (req, res, next) => {
  try {
    const { action } = req.body;
    const statusMap: Record<string, "approved" | "rejected" | "blocked"> = {
      approve: "approved",
      reject: "rejected",
      block: "blocked"
    };

    const newStatus = statusMap[action];
    if (!newStatus) {
      addFlash(req, "error", "Invalid action");
      return res.redirect(`/admin/users/${req.params.id}`);
    }

    const updates: any = { status: newStatus, updatedAt: new Date() };
    if (newStatus === "approved") updates.approvedAt = new Date();

    const user = await prisma.user.update({
      where: { id: req.params.id },
      data: updates
    });

    await prisma.auditLog.create({
      data: {
        actorType: "admin",
        actorId: req.sessionID,
        action: `user_${newStatus}`,
        metadata: { userId: user.id },
        ipAddress: req.ip
      }
    });

    addFlash(req, "success", `User ${newStatus}`);
    return res.redirect(`/admin/users/${user.id}`);
  } catch (err) {
    next(err);
  }
});

router.post("/users/:id/notes", async (req, res, next) => {
  try {
    const { admin_notes } = req.body;
    const user = await prisma.user.update({
      where: { id: req.params.id },
      data: { adminNotes: admin_notes, updatedAt: new Date() }
    });

    await prisma.auditLog.create({
      data: {
        actorType: "admin",
        actorId: req.sessionID,
        action: "user_notes_update",
        metadata: { userId: user.id },
        ipAddress: req.ip
      }
    });

    addFlash(req, "success", "Notes updated");
    return res.redirect(`/admin/users/${user.id}`);
  } catch (err) {
    next(err);
  }
});

router.post("/logout", (req, res) => {
  req.session.isAdmin = false;
  req.session.destroy(() => res.redirect("/admin/login"));
});

export default router;
