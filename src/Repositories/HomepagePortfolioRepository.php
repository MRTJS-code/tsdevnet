<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepagePortfolioRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM portfolio_items';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM portfolio_items WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO portfolio_items (title, slug, short_summary, category, problem_text, approach_text, outcome_text, tech_text, thumbnail_path, repo_url, demo_url, is_gated, is_featured, status, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['title'],
            $data['slug'] ?? $this->slugify($data['title']),
            ($data['summary'] ?? $data['short_summary'] ?? '') ?: null,
            ($data['category'] ?? '') ?: null,
            ($data['problem_text'] ?? '') ?: null,
            ($data['approach_text'] ?? '') ?: null,
            ($data['outcome'] ?? $data['outcome_text'] ?? '') ?: null,
            ($data['tech_text'] ?? '') ?: null,
            ($data['thumbnail_path'] ?? '') ?: null,
            ($data['repo_url'] ?? '') ?: null,
            ($data['demo_url'] ?? $data['link_url'] ?? '') ?: null,
            !empty($data['is_gated']) ? 1 : 0,
            !empty($data['is_featured']) ? 1 : 0,
            ($data['status'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE portfolio_items
             SET title = ?, slug = ?, short_summary = ?, category = ?, problem_text = ?, approach_text = ?, outcome_text = ?, tech_text = ?, thumbnail_path = ?, repo_url = ?, demo_url = ?, is_gated = ?, is_featured = ?, status = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['title'],
            $data['slug'] ?? $this->slugify($data['title']),
            ($data['summary'] ?? $data['short_summary'] ?? '') ?: null,
            ($data['category'] ?? '') ?: null,
            ($data['problem_text'] ?? '') ?: null,
            ($data['approach_text'] ?? '') ?: null,
            ($data['outcome'] ?? $data['outcome_text'] ?? '') ?: null,
            ($data['tech_text'] ?? '') ?: null,
            ($data['thumbnail_path'] ?? '') ?: null,
            ($data['repo_url'] ?? '') ?: null,
            ($data['demo_url'] ?? $data['link_url'] ?? '') ?: null,
            !empty($data['is_gated']) ? 1 : 0,
            !empty($data['is_featured']) ? 1 : 0,
            ($data['status'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'summary' => $row['short_summary'] ?? '',
            'outcome' => $row['outcome_text'] ?? '',
            'link_url' => $row['demo_url'] ?: ($row['repo_url'] ?? ''),
            'link_label' => $row['demo_url'] ? 'View project' : (!empty($row['repo_url']) ? 'View repository' : ''),
            'sort_order' => (int) ($row['display_order'] ?? 0),
        ];
    }

    private function slugify(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-'));
        return $slug !== '' ? $slug : 'portfolio-item';
    }
}
