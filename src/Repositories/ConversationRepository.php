<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ConversationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, string $tier, string $ip, string $userAgent): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversations (user_id, tier_at_time, started_at, ip_address, user_agent)
             VALUES (?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$userId, $tier, $ip, $userAgent]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM conversations WHERE user_id = ? ORDER BY started_at DESC');
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

