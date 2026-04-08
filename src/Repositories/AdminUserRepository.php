<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AdminUserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(string $email, string $passwordHash, string $displayName): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_users (email, password_hash, display_name, is_active, created_at, updated_at)
             VALUES (?, ?, ?, 1, NOW(), NOW())'
        );
        $stmt->execute([$email, $passwordHash, $displayName]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE admin_users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function countActive(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM admin_users WHERE is_active = 1')->fetchColumn();
    }
}

