<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleRichTextSectionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByModuleId(int $moduleId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM module_rich_text_sections WHERE module_id = ? LIMIT 1');
        $stmt->execute([$moduleId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function upsertForModule(int $moduleId, array $data): void
    {
        $existing = $this->findByModuleId($moduleId);
        if ($existing) {
            $stmt = $this->pdo->prepare(
                'UPDATE module_rich_text_sections
                 SET body_text = ?, cta_label = ?, cta_url = ?, secondary_cta_label = ?, secondary_cta_url = ?, updated_at = NOW()
                 WHERE module_id = ?'
            );
            $stmt->execute([
                $this->nullableString($data['body_text'] ?? null),
                $this->nullableString($data['cta_label'] ?? null),
                $this->nullableString($data['cta_url'] ?? null),
                $this->nullableString($data['secondary_cta_label'] ?? null),
                $this->nullableString($data['secondary_cta_url'] ?? null),
                $moduleId,
            ]);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO module_rich_text_sections (module_id, body_text, cta_label, cta_url, secondary_cta_label, secondary_cta_url, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $moduleId,
            $this->nullableString($data['body_text'] ?? null),
            $this->nullableString($data['cta_label'] ?? null),
            $this->nullableString($data['cta_url'] ?? null),
            $this->nullableString($data['secondary_cta_label'] ?? null),
            $this->nullableString($data['secondary_cta_url'] ?? null),
        ]);
    }

    public function deleteByModuleId(int $moduleId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM module_rich_text_sections WHERE module_id = ?');
        $stmt->execute([$moduleId]);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }
}
