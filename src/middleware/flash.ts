import { NextFunction, Request, Response } from "express";

export function flashMiddleware(req: Request, res: Response, next: NextFunction) {
  res.locals.flash = req.session.flash || [];
  req.session.flash = [];
  next();
}

export function addFlash(req: Request, type: string, message: string) {
  req.session.flash = req.session.flash || [];
  req.session.flash.push({ type, message });
}
