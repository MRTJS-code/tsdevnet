<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class MessageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function add(int $conversationId, string $sender, string $content): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (conversation_id, sender, content, created_at)
             VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$conversationId, $sender, $content]);
    }

    public function recentForUser(int $userId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, c.started_at
             FROM messages m
             INNER JOIN conversations c ON c.id = m.conversation_id
             WHERE c.user_id = ?
             ORDER BY m.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

