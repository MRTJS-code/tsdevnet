import { NextFunction, Request, Response, RequestHandler } from "express";

export const flashMiddleware: RequestHandler = (req: Request, res: Response, next: NextFunction) => {
  res.locals.flash = req.session.flash || [];
  req.session.flash = [];
  next();
};

export function addFlash(req: Request, type: string, message: string) {
  req.session.flash = req.session.flash || [];
  req.session.flash.push({ type, message });
}
