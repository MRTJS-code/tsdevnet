import { Router } from "express";
import { prisma } from "../db";
import { TurnstileService } from "../services/TurnstileService";
import { MailService } from "../services/MailService";
import { NotificationService } from "../services/NotificationService";
import { config } from "../config";
import { generateToken, hashToken } from "../utils/tokens";
import { addFlash } from "../middleware/flash";
import { rateLimitByIp } from "../middleware/rateLimit";

const router = Router();

router.get("/", (req, res) => {
  res.render("landing", { title: "Tony Smith | Recruiter", csrfToken: req.csrfToken() });
});

router.get("/signup", (req, res) => {
  res.render("signup", { title: "Request Access", csrfToken: req.csrfToken() });
});

router.post(
  "/signup",
  rateLimitByIp("signup", 8, 15 * 60 * 1000),
  async (req, res, next) => {
    try {
      const turnstileToken =
        req.body["cf-turnstile-response"] || req.body.turnstile_token;
      const validCaptcha = await TurnstileService.verify(turnstileToken, req.ip);
      if (!validCaptcha) {
        addFlash(req, "error", "Verification failed. Please try again.");
        return res.redirect("/signup");
      }

      const { name, email, company, role_type, linkedin_url, hiring_for, consent } = req.body;
      const normalizedEmail = (email || "").toLowerCase().trim();
      if (!normalizedEmail) {
        addFlash(req, "error", "Email is required.");
        return res.redirect("/signup");
      }

      const consentAt = consent ? new Date() : null;
      const now = new Date();

      let user = await prisma.user.findUnique({ where: { email: normalizedEmail } });
      const isNewUser = !user;

      if (!user) {
        user = await prisma.user.create({
          data: {
            name,
            email: normalizedEmail,
            company,
            roleType: role_type,
            linkedinUrl: linkedin_url,
            hiringFor: hiring_for,
            consentAt,
            status: "pending",
            createdAt: now,
            updatedAt: now
          }
        });
      } else {
        user = await prisma.user.update({
          where: { id: user.id },
          data: {
            name: name || user.name,
            company: company || user.company,
            roleType: role_type || user.roleType,
            linkedinUrl: linkedin_url || user.linkedinUrl,
            hiringFor: hiring_for || user.hiringFor,
            consentAt: consent ? new Date() : user.consentAt,
            status: user.status === "rejected" ? "pending" : user.status,
            updatedAt: now
          }
        });
      }

      if (user.status === "blocked") {
        return res.render("confirm", {
          title: "Check your email",
          message: "If your account is eligible, we\'ve sent you a magic link.",
          devMagicLink: null,
          csrfToken: req.csrfToken()
        });
      }

      const token = generateToken();
      const tokenHash = hashToken(token);
      const expiresAt = new Date(Date.now() + config.tokenExpiryMinutes * 60 * 1000);

      await prisma.accessToken.create({
        data: {
          userId: user.id,
          tokenHash,
          expiresAt,
          ipAddress: req.ip,
          userAgent: req.headers["user-agent"] || ""
        }
      });

      const magicLink = `${config.appUrl}/verify?token=${token}`;
      const message = `Hi ${user.name || "there"},\n\nComplete your login: ${magicLink}\nThis link expires in ${config.tokenExpiryMinutes} minutes.`;
      await MailService.sendMail({
        to: user.email,
        subject: "Your magic link",
        text: message
      });

      if (isNewUser) {
        await NotificationService.notifyOwnerNewSignup(user);
      }

      return res.render("confirm", {
        title: "Check your email",
        message: "If your account is eligible, we\'ve sent you a magic link.",
        devMagicLink: config.env === "dev" ? magicLink : null,
        csrfToken: req.csrfToken()
      });
    } catch (err) {
      return next(err);
    }
  }
);

router.get("/login", (req, res) => {
  res.render("login", { title: "Log in", csrfToken: req.csrfToken() });
});

router.post(
  "/login",
  rateLimitByIp("login", 10, 15 * 60 * 1000),
  async (req, res, next) => {
    try {
      const { email } = req.body;
      const normalizedEmail = (email || "").toLowerCase().trim();
      const turnstileToken =
        req.body["cf-turnstile-response"] || req.body.turnstile_token;
      const validCaptcha = await TurnstileService.verify(turnstileToken, req.ip);
      if (!validCaptcha) {
        addFlash(req, "error", "Verification failed. Please try again.");
        return res.redirect("/login");
      }

      const user = await prisma.user.findUnique({ where: { email: normalizedEmail } });

      if (user && user.status !== "blocked" && user.status !== "rejected") {
        const token = generateToken();
        const tokenHash = hashToken(token);
        const expiresAt = new Date(Date.now() + config.tokenExpiryMinutes * 60 * 1000);
        await prisma.accessToken.create({
          data: {
            userId: user.id,
            tokenHash,
            expiresAt,
            ipAddress: req.ip,
            userAgent: req.headers["user-agent"] || ""
          }
        });

        const magicLink = `${config.appUrl}/verify?token=${token}`;
        const message = `Hi ${user.name || "there"},\n\nComplete your login: ${magicLink}\nThis link expires in ${config.tokenExpiryMinutes} minutes.`;
        await MailService.sendMail({
          to: user.email,
          subject: "Your magic link",
          text: message
        });

        return res.render("confirm", {
          title: "Check your email",
          message: "If your account is eligible, we\'ve sent you a magic link.",
          devMagicLink: config.env === "dev" ? magicLink : null,
          csrfToken: req.csrfToken()
        });
      }

      return res.render("confirm", {
        title: "Check your email",
        message: "If your account is eligible, we\\'ve sent you a magic link.",
        devMagicLink: null,
        csrfToken: req.csrfToken()
      });
    } catch (err) {
      return next(err);
    }
  }
);

router.get("/verify", async (req, res, next) => {
  try {
    const token = req.query.token?.toString();
    if (!token) {
      return res.status(400).render("error", {
        title: "Invalid link",
        message: "Token missing.",
        csrfToken: req.csrfToken()
      });
    }

    const tokenHash = hashToken(token);
    const now = new Date();

    const accessToken = await prisma.accessToken.findFirst({
      where: {
        tokenHash,
        usedAt: null,
        expiresAt: { gt: now }
      },
      include: { user: true }
    });

    if (
      !accessToken ||
      !accessToken.user ||
      accessToken.user.status === "blocked" ||
      accessToken.user.status === "rejected"
    ) {
      return res.status(400).render("error", {
        title: "Invalid link",
        message: "This link is invalid or expired.",
        csrfToken: req.csrfToken()
      });
    }

    await prisma.$transaction([
      prisma.accessToken.update({
        where: { id: accessToken.id },
        data: { usedAt: now }
      }),
      prisma.user.update({
        where: { id: accessToken.userId },
        data: { lastLoginAt: now, updatedAt: now }
      })
    ]);

    req.session.userId = accessToken.userId;
    req.session.conversationId = undefined;
    return res.redirect("/app");
  } catch (err) {
    return next(err);
  }
});

router.post("/logout", (req, res) => {
  req.session.destroy(() => {
    res.redirect("/");
  });
});

export default router;
