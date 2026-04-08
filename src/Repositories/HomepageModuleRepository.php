<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageModuleRepository
{
    private const ALLOWED_TYPES = [
        'rich_text',
        'experience_timeline',
        'certifications',
        'technology_groups',
        'featured_portfolio',
        'testimonials',
        'cta_info',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM homepage_modules ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listActive(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM homepage_modules WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_modules WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findByModuleKey(string $moduleKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_modules WHERE module_key = ? LIMIT 1');
        $stmt->execute([trim($moduleKey)]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, media_document_id, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute($this->params($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE homepage_modules
             SET module_key = ?, module_type = ?, eyebrow = ?, title = ?, intro_text = ?, anchor_id = ?, style_variant = ?, group_key = ?, media_document_id = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $params = $this->params($data);
        $params[] = $id;
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM homepage_modules WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function allowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    private function params(array $data): array
    {
        $moduleType = trim((string) ($data['module_type'] ?? 'rich_text'));
        if (!in_array($moduleType, self::ALLOWED_TYPES, true)) {
            $moduleType = 'rich_text';
        }

        return [
            trim((string) ($data['module_key'] ?? '')),
            $moduleType,
            $this->nullableString($data['eyebrow'] ?? null),
            $this->nullableString($data['title'] ?? null),
            $this->nullableString($data['intro_text'] ?? null),
            $this->nullableString($data['anchor_id'] ?? null),
            $this->nullableString($data['style_variant'] ?? null),
            $this->nullableString($data['group_key'] ?? null),
            !empty($data['media_document_id']) ? (int) $data['media_document_id'] : null,
            (int) ($data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }
}
