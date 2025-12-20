-- Extend schema for live chat + admin replies
ALTER TYPE "HandoffType" ADD VALUE IF NOT EXISTS ''meeting'';

CREATE TYPE "ConversationStatus" AS ENUM (''open'', ''closed'');
ALTER TABLE "Conversation" ADD COLUMN IF NOT EXISTS "status" "ConversationStatus" NOT NULL DEFAULT ''open'';
ALTER TABLE "Conversation" ADD COLUMN IF NOT EXISTS "lastActivityAt" TIMESTAMPTZ NOT NULL DEFAULT NOW();

ALTER TABLE "Message" ADD COLUMN IF NOT EXISTS "metadata" JSONB;
ALTER TABLE "Message" ADD COLUMN IF NOT EXISTS "isOwnerReply" BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE "HandoffRequest" ADD COLUMN IF NOT EXISTS "conversationId" UUID;
ALTER TABLE "HandoffRequest" ADD COLUMN IF NOT EXISTS "updatedAt" TIMESTAMPTZ NOT NULL DEFAULT NOW();
ALTER TABLE "HandoffRequest" ALTER COLUMN "type" TYPE "HandoffType" USING "type"::text::"HandoffType";
ALTER TABLE "HandoffRequest" ADD CONSTRAINT handoff_conversation_fk FOREIGN KEY ("conversationId") REFERENCES "Conversation"("id") ON DELETE SET NULL;

CREATE OR REPLACE FUNCTION set_handoff_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW."updatedAt" = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS handoff_updated_at ON "HandoffRequest";
CREATE TRIGGER handoff_updated_at BEFORE UPDATE ON "HandoffRequest" FOR EACH ROW EXECUTE PROCEDURE set_handoff_updated_at();
