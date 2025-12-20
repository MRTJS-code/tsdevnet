import { NextFunction, Request, Response } from "express";
import { prisma } from "../db";

async function checkRateLimit(
  scope: string,
  key: string,
  limit: number,
  windowMs: number
): Promise<boolean> {
  const now = Date.now();
  const windowStart = new Date(Math.floor(now / windowMs) * windowMs);

  try {
    await prisma.$transaction(async (tx) => {
      const existing = await tx.rateLimit.findFirst({
        where: { scope, key, windowStart }
      });

      if (existing) {
        if (existing.count >= limit) {
          throw new Error("RATE_LIMIT");
        }
        await tx.rateLimit.update({
          where: { id: existing.id },
          data: { count: { increment: 1 } }
        });
      } else {
        await tx.rateLimit.create({
          data: { scope, key, windowStart, count: 1 }
        });
      }
    });
    return true;
  } catch (err: any) {
    if (err?.message === "RATE_LIMIT") return false;
    throw err;
  }
}

export function rateLimitByIp(scope: string, limit: number, windowMs: number) {
  return async (req: Request, res: Response, next: NextFunction) => {
    const ip = req.ip || req.headers["x-forwarded-for"]?.toString() || "unknown";
    const allowed = await checkRateLimit(scope, ip, limit, windowMs);
    if (!allowed) {
      return res.status(429).render("error", {
        title: "Slow down",
        message: "Too many requests. Please try again shortly.",
        csrfToken: req.csrfToken ? req.csrfToken() : undefined
      });
    }
    return next();
  };
}

export async function enforceUserRateLimit(
  userId: string,
  scope: string,
  limit: number,
  windowMs: number
): Promise<boolean> {
  return checkRateLimit(scope, `user:${userId}`, limit, windowMs);
}
