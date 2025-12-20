import { NextFunction, Request, Response } from "express";
import { prisma } from "../db";

export async function loadCurrentUser(
  req: Request,
  res: Response,
  next: NextFunction
) {
  try {
    if (!req.session.userId) {
      res.locals.currentUser = null;
      return next();
    }

    const user = await prisma.user.findUnique({ where: { id: req.session.userId } });
    if (!user) {
      req.session.userId = undefined;
      res.locals.currentUser = null;
      return next();
    }
    req.currentUser = user;
    res.locals.currentUser = user;
    return next();
  } catch (err) {
    return next(err);
  }
}

export function requireUser(req: Request, res: Response, next: NextFunction) {
  if (!req.session.userId) {
    return res.redirect("/login");
  }
  return next();
}

export function requireAdmin(req: Request, res: Response, next: NextFunction) {
  if (!req.session.isAdmin) {
    return res.redirect("/admin/login");
  }
  return next();
}
