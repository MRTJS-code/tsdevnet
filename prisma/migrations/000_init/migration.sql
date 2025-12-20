-- Initial schema
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE TYPE "UserStatus" AS ENUM (''pending'', ''approved'', ''rejected'', ''blocked'');
CREATE TYPE "SenderType" AS ENUM (''user'', ''assistant'');
CREATE TYPE "ActorType" AS ENUM (''admin'', ''user'');
CREATE TYPE "HandoffType" AS ENUM (''approval_request'', ''meeting_request'', ''live_chat'');
CREATE TYPE "HandoffStatus" AS ENUM (''new'', ''acknowledged'', ''closed'');

CREATE TABLE "User" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "name" TEXT,
  "email" TEXT NOT NULL UNIQUE,
  "company" TEXT,
  "roleType" TEXT,
  "linkedinUrl" TEXT,
  "hiringFor" TEXT,
  "consentAt" TIMESTAMPTZ,
  "status" "UserStatus" NOT NULL DEFAULT ''pending'',
  "adminNotes" TEXT,
  "createdAt" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  "updatedAt" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  "approvedAt" TIMESTAMPTZ,
  "lastLoginAt" TIMESTAMPTZ
);

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW."updatedAt" = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER user_updated_at BEFORE UPDATE ON "User" FOR EACH ROW EXECUTE PROCEDURE set_updated_at();

CREATE TABLE "AccessToken" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "userId" UUID NOT NULL REFERENCES "User"("id") ON DELETE CASCADE,
  "tokenHash" TEXT NOT NULL UNIQUE,
  "expiresAt" TIMESTAMPTZ NOT NULL,
  "usedAt" TIMESTAMPTZ,
  "createdAt" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  "ipAddress" TEXT,
  "userAgent" TEXT
);

CREATE TABLE "Conversation" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "userId" UUID NOT NULL REFERENCES "User"("id") ON DELETE CASCADE,
  "tierAtTime" TEXT NOT NULL,
  "startedAt" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  "endedAt" TIMESTAMPTZ,
  "ipAddress" TEXT,
  "userAgent" TEXT
);

CREATE TABLE "Message" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "conversationId" UUID NOT NULL REFERENCES "Conversation"("id") ON DELETE CASCADE,
  "sender" "SenderType" NOT NULL,
  "content" TEXT NOT NULL,
  "createdAt" TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE "RateLimit" (
  "id" SERIAL PRIMARY KEY,
  "scope" TEXT NOT NULL,
  "key" TEXT NOT NULL,
  "windowStart" TIMESTAMPTZ NOT NULL,
  "count" INTEGER NOT NULL,
  CONSTRAINT rate_limit_unique UNIQUE("scope", "key", "windowStart")
);

CREATE TABLE "AuditLog" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "actorType" "ActorType" NOT NULL,
  "actorId" TEXT,
  "action" TEXT NOT NULL,
  "metadata" JSONB,
  "createdAt" TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  "ipAddress" TEXT
);

CREATE TABLE "HandoffRequest" (
  "id" UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  "userId" UUID NOT NULL REFERENCES "User"("id") ON DELETE CASCADE,
  "type" "HandoffType" NOT NULL,
  "message" TEXT,
  "status" "HandoffStatus" NOT NULL DEFAULT ''new'',
  "createdAt" TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE "Session" (
  "sid" TEXT PRIMARY KEY,
  "sess" JSONB NOT NULL,
  "expire" TIMESTAMPTZ NOT NULL
);

CREATE INDEX IF NOT EXISTS "Message_conversation_idx" ON "Message"("conversationId");
