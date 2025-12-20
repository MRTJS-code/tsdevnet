<?php
declare(strict_types=1);

class ChatService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function startConversation(int $userId, string $tier, string $ip, string $ua): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversations (user_id, tier_at_time, started_at, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$userId, $tier, $ip, $ua]);
        return (int)$this->pdo->lastInsertId();
    }

    public function addMessage(int $conversationId, string $sender, string $content): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (conversation_id, sender, content, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$conversationId, $sender, $content]);
    }

    public function generateReply(string $message): string
    {
        $lower = strtolower($message);
        if (strpos($lower, 'role') !== false) {
            return "Quick take: You want clarity on the role. I’ll summarize responsibilities, scope, and success metrics. Approval unlocks a deeper market view.";
        }
        if (strpos($lower, 'skills') !== false) {
            return "Skills overview:\n- Core: backend APIs, PHP/Node, SQL\n- Nice-to-have: cloud, security, observability\n- Team fit: pragmatic, low-ego collaborators.";
        }
        return "Demo response: I can give you a concise candidate brief now; detailed market signals and sourcing tactics will unlock once approved.";
    }
}
