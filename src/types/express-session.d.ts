import "express-session";

declare module "express-session" {
  interface SessionData {
    userId?: string;
    conversationId?: string;
    isAdmin?: boolean;
    flash?: { type: string; message: string }[];
  }
}
