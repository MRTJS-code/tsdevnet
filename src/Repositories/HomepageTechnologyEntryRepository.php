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
        $query = 'SELECT e.*, g.group_label AS group_title, g.group_key
                  FROM profile_technologies e
                  INNER JOIN profile_technology_groups g ON g.id = e.technology_group_id';
        if ($activeOnly) {
            $query .= ' WHERE e.is_active = 1 AND g.is_active = 1';
        }
        $query .= ' ORDER BY g.display_order ASC, e.display_order ASC, e.id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function listGroupedByGroupIds(array $groupIds, bool $activeOnly = true): array
    {
        if ($groupIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($groupIds), '?'));
        $query = 'SELECT * FROM profile_technologies WHERE technology_group_id IN (' . $placeholders . ')';
        if ($activeOnly) {
            $query .= ' AND is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($groupIds));

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entry) {
            $mapped = $this->mapRow($entry);
            $grouped[(int) $mapped['group_id']][] = $mapped;
        }

        return $grouped;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profile_technologies WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO profile_technologies (technology_group_id, name, logo_slug, logo_path, notes, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            (int) ($data['group_id'] ?? $data['technology_group_id']),
            $data['label'] ?? $data['name'],
            $data['logo_slug'] ?? null,
            $data['logo_path'] ?? null,
            ($data['detail_text'] ?? $data['notes'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE profile_technologies
             SET technology_group_id = ?, name = ?, logo_slug = ?, logo_path = ?, notes = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            (int) ($data['group_id'] ?? $data['technology_group_id']),
            $data['label'] ?? $data['name'],
            $data['logo_slug'] ?? null,
            $data['logo_path'] ?? null,
            ($data['detail_text'] ?? $data['notes'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'group_id' => (int) $row['technology_group_id'],
            'label' => $row['name'],
            'detail_text' => $row['notes'] ?? '',
            'sort_order' => (int) ($row['display_order'] ?? 0),
        ];
    }
}
