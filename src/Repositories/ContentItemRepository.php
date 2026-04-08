<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ContentItemRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByBlockId(int $blockId, bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM content_items WHERE block_id = ?';
        if ($activeOnly) {
            $query .= ' AND is_active = 1';
        }
        $query .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$blockId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAllWithBlock(): array
    {
        $stmt = $this->pdo->query(
            'SELECT ci.*, cb.section_key
             FROM content_items ci
             INNER JOIN content_blocks cb ON cb.id = ci.block_id
             ORDER BY cb.sort_order ASC, ci.sort_order ASC, ci.id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listGroupedByBlockIds(array $blockIds, bool $activeOnly = true): array
    {
        if ($blockIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($blockIds), '?'));
        $query = 'SELECT * FROM content_items WHERE block_id IN (' . $placeholders . ')';
        if ($activeOnly) {
            $query .= ' AND is_active = 1';
        }
        $query .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($blockIds));

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $grouped[(int) $item['block_id']][] = $item;
        }

        return $grouped;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM content_items WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO content_items (block_id, item_key, label, title, body_text, link_url, meta_json, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            (int) $data['block_id'],
            $data['item_key'] ?: null,
            $data['label'] ?: null,
            $data['title'] ?: null,
            $data['body_text'] ?: null,
            $data['link_url'] ?: null,
            $data['meta_json'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE content_items
             SET block_id = ?, item_key = ?, label = ?, title = ?, body_text = ?, link_url = ?, meta_json = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            (int) $data['block_id'],
            $data['item_key'] ?: null,
            $data['label'] ?: null,
            $data['title'] ?: null,
            $data['body_text'] ?: null,
            $data['link_url'] ?: null,
            $data['meta_json'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }
}
