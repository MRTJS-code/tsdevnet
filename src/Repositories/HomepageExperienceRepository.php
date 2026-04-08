<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageExperienceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM profile_experience';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profile_experience WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO profile_experience (role_title, company_name, start_date, end_date, is_current, summary_text, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['role_title'],
            $data['organisation'] ?? $data['company_name'],
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            !empty($data['is_current']) ? 1 : 0,
            ($data['summary'] ?? $data['summary_text'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE profile_experience
             SET role_title = ?, company_name = ?, start_date = ?, end_date = ?, is_current = ?, summary_text = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['role_title'],
            $data['organisation'] ?? $data['company_name'],
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            !empty($data['is_current']) ? 1 : 0,
            ($data['summary'] ?? $data['summary_text'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM profile_experience WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'organisation' => $row['company_name'],
            'period_label' => $this->periodLabel($row),
            'summary' => $row['summary_text'] ?? '',
            'sort_order' => (int) ($row['display_order'] ?? 0),
            'highlight_text' => '',
        ];
    }

    private function periodLabel(array $row): string
    {
        $start = !empty($row['start_date']) ? substr((string) $row['start_date'], 0, 7) : 'Date TBD';
        $end = !empty($row['is_current']) ? 'Present' : (!empty($row['end_date']) ? substr((string) $row['end_date'], 0, 7) : 'Date TBD');

        return $start . ' - ' . $end;
    }
}
