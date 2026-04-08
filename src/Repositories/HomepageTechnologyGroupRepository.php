<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageTechnologyGroupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM profile_technology_groups';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profile_technology_groups WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function findByGroupKey(string $groupKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profile_technology_groups WHERE group_key = ? LIMIT 1');
        $stmt->execute([$groupKey]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO profile_technology_groups (group_key, group_label, intro_text, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['group_key'],
            $data['title'] ?? $data['group_label'],
            ($data['intro_text'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE profile_technology_groups
             SET group_key = ?, group_label = ?, intro_text = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['group_key'],
            $data['title'] ?? $data['group_label'],
            ($data['intro_text'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function upsertByGroupKey(array $data): int
    {
        $existing = $this->findByGroupKey($data['group_key']);
        if ($existing) {
            $this->update((int) $existing['id'], $data);
            return (int) $existing['id'];
        }

        return $this->create($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM profile_technology_groups WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'title' => $row['group_label'],
            'sort_order' => (int) ($row['display_order'] ?? 0),
        ];
    }
}
