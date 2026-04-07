ALTER TABLE users
    ADD INDEX idx_users_status_created_at (status, created_at);

ALTER TABLE access_tokens
    ADD INDEX idx_access_tokens_user_created_at (user_id, created_at);

ALTER TABLE conversations
    ADD INDEX idx_conversations_user_started_at (user_id, started_at);

ALTER TABLE messages
    ADD INDEX idx_messages_conversation_created_at (conversation_id, created_at);

ALTER TABLE audit_log
    ADD INDEX idx_audit_log_actor_created_at (actor_type, created_at);
