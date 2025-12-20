import { PrismaClient } from "@prisma/client";

export const prisma = new PrismaClient({
  log: process.env.APP_ENV === "dev" ? ["query", "error", "warn"] : ["error"]
});
