<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class TokenRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, string $tokenHash, string $expiresAt, string $ip, string $userAgent): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO access_tokens (user_id, token_hash, expires_at, created_at, ip_address, user_agent)
             VALUES (?, ?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$userId, $tokenHash, $expiresAt, $ip, $userAgent]);
    }

    public function findActiveByHash(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT at.id, at.user_id, at.expires_at, at.used_at, u.status, u.name, u.email
             FROM access_tokens at
             INNER JOIN users u ON u.id = at.user_id
             WHERE at.token_hash = ?
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE access_tokens SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }
}

