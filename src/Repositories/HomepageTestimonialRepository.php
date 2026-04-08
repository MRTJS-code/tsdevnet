<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageTestimonialRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM homepage_testimonials';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_testimonials WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_testimonials (quote_text, person_name, person_title, organisation, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['quote_text'],
            $data['person_name'],
            $data['person_title'] ?: null,
            $data['organisation'] ?: null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE homepage_testimonials
             SET quote_text = ?, person_name = ?, person_title = ?, organisation = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['quote_text'],
            $data['person_name'],
            $data['person_title'] ?: null,
            $data['organisation'] ?: null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }
}
