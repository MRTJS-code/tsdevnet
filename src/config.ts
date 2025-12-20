import dotenv from "dotenv";

dotenv.config();

const toBool = (val: string | undefined, fallback = false) => {
  if (val === undefined) return fallback;
  return ["1", "true", "yes", "on"].includes(val.toLowerCase());
};

export const config = {
  env: process.env.APP_ENV || "dev",
  port: parseInt(process.env.PORT || "3000", 10),
  appUrl: process.env.APP_URL || "http://localhost:3000",
  sessionSecret: process.env.SESSION_SECRET || "change-me",
  sessionSecure: toBool(process.env.SESSION_SECURE, false),
  databaseUrl: process.env.DATABASE_URL || "",
  turnstile: {
    siteKey: process.env.TURNSTILE_SITE_KEY || "",
    secretKey: process.env.TURNSTILE_SECRET_KEY || ""
  },
  smtp: {
    host: process.env.SMTP_HOST || "",
    port: parseInt(process.env.SMTP_PORT || "587", 10),
    user: process.env.SMTP_USER || "",
    pass: process.env.SMTP_PASS || "",
    fromEmail: process.env.SMTP_FROM_EMAIL || "noreply@example.com",
    fromName: process.env.SMTP_FROM_NAME || "Tony Smith"
  },
  ownerEmail: process.env.OWNER_EMAIL || "",
  adminPassword: process.env.ADMIN_PASSWORD || "",
  devMailbox: process.env.DEV_MAILBOX || "console",
  publicBaseUrl: process.env.PUBLIC_BASE_URL || process.env.APP_URL || "http://localhost:3000",
  telegram: {
    botToken: process.env.TELEGRAM_BOT_TOKEN || "",
    ownerChatId: process.env.OWNER_TELEGRAM_CHAT_ID || ""
  },
  tokenExpiryMinutes: 15,
  demoLimitPerDay: 5,
  fullLimitPerDay: 50
};

export const isProd = config.env === "prod";
