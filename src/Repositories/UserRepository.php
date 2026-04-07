<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, company, role_type, linkedin_url, hiring_for, consent_at, status, admin_notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['company'],
            $data['role_type'],
            $data['linkedin_url'] ?: null,
            $data['hiring_for'] ?: null,
            $data['consent_at'],
            $data['status'] ?? 'pending',
            $data['admin_notes'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function touchLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function updateStatus(int $userId, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET status = ?, approved_at = CASE WHEN ? = "approved" THEN NOW() ELSE approved_at END, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$status, $status, $userId]);
    }

    public function updateNotes(int $userId, string $notes): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET admin_notes = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$notes, $userId]);
    }

    public function listRecentByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, company, role_type, created_at
             FROM users
             WHERE status = ?
             ORDER BY created_at DESC'
        );
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

