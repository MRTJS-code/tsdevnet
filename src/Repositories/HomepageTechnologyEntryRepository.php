<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageTechnologyEntryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAllWithGroup(bool $activeOnly = false): array
    {
        $query = 'SELECT e.*, g.title AS group_title, g.group_key
                  FROM homepage_technology_entries e
                  INNER JOIN homepage_technology_groups g ON g.id = e.group_id';
        if ($activeOnly) {
            $query .= ' WHERE e.is_active = 1 AND g.is_active = 1';
        }
        $query .= ' ORDER BY g.sort_order ASC, e.sort_order ASC, e.id ASC';

        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listGroupedByGroupIds(array $groupIds, bool $activeOnly = true): array
    {
        if ($groupIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($groupIds), '?'));
        $query = 'SELECT * FROM homepage_technology_entries WHERE group_id IN (' . $placeholders . ')';
        if ($activeOnly) {
            $query .= ' AND is_active = 1';
        }
        $query .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($groupIds));

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entry) {
            $grouped[(int) $entry['group_id']][] = $entry;
        }

        return $grouped;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_technology_entries WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_technology_entries (group_id, label, detail_text, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            (int) $data['group_id'],
            $data['label'],
            $data['detail_text'] ?: null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE homepage_technology_entries
             SET group_id = ?, label = ?, detail_text = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            (int) $data['group_id'],
            $data['label'],
            $data['detail_text'] ?: null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }
}
