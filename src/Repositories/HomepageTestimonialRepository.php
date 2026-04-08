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
        $query = 'SELECT * FROM testimonials';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM testimonials WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO testimonials (quote_text, source_name, source_title, source_context, is_featured, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['quote_text'],
            $data['person_name'] ?? $data['source_name'],
            ($data['person_title'] ?? $data['source_title'] ?? '') ?: null,
            ($data['organisation'] ?? $data['source_context'] ?? '') ?: null,
            !empty($data['is_featured']) ? 1 : 0,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE testimonials
             SET quote_text = ?, source_name = ?, source_title = ?, source_context = ?, is_featured = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['quote_text'],
            $data['person_name'] ?? $data['source_name'],
            ($data['person_title'] ?? $data['source_title'] ?? '') ?: null,
            ($data['organisation'] ?? $data['source_context'] ?? '') ?: null,
            !empty($data['is_featured']) ? 1 : 0,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'person_name' => $row['source_name'],
            'person_title' => $row['source_title'] ?? '',
            'organisation' => $row['source_context'] ?? '',
            'sort_order' => (int) ($row['display_order'] ?? 0),
        ];
    }
}
