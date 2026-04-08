<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ContentBlockRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM content_blocks ORDER BY ' . $this->orderBySql());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listActive(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM content_blocks WHERE is_active = 1 ORDER BY ' . $this->orderBySql());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM content_blocks WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findBySectionKey(string $sectionKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM content_blocks WHERE section_key = ? LIMIT 1');
        $stmt->execute([$sectionKey]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO content_blocks (section_key, homepage_position, title, subtitle, body_text, meta_json, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['section_key'],
            $this->normalizeHomepagePosition($data['homepage_position'] ?? null),
            $data['title'] ?: null,
            $data['subtitle'] ?: null,
            $data['body_text'] ?: null,
            $data['meta_json'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE content_blocks
             SET section_key = ?, homepage_position = ?, title = ?, subtitle = ?, body_text = ?, meta_json = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['section_key'],
            $this->normalizeHomepagePosition($data['homepage_position'] ?? null),
            $data['title'] ?: null,
            $data['subtitle'] ?: null,
            $data['body_text'] ?: null,
            $data['meta_json'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function upsertBySectionKey(array $data): int
    {
        $existing = $this->findBySectionKey($data['section_key']);
        if ($existing) {
            $this->update((int) $existing['id'], $data);
            return (int) $existing['id'];
        }

        return $this->create($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM content_blocks WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function normalizeHomepagePosition(?string $position): string
    {
        return match ($position) {
            'middle', 'bottom' => $position,
            default => 'top',
        };
    }

    private function orderBySql(): string
    {
        return "CASE homepage_position
                    WHEN 'top' THEN 1
                    WHEN 'middle' THEN 2
                    WHEN 'bottom' THEN 3
                    ELSE 4
                END ASC, sort_order ASC, section_key ASC";
    }
}
