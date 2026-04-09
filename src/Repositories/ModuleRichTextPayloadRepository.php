<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleRichTextPayloadRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByModuleId(int $moduleId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM module_rich_text_payloads WHERE module_id = ? LIMIT 1');
        $stmt->execute([$moduleId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function upsertForModule(int $moduleId, array $data): void
    {
        $params = [
            $this->nullableString($data['body_text'] ?? null),
            $this->nullableString($data['primary_cta_label'] ?? null),
            $this->nullableString($data['primary_cta_url'] ?? null),
            $this->nullableString($data['secondary_cta_label'] ?? null),
            $this->nullableString($data['secondary_cta_url'] ?? null),
            $moduleId,
        ];

        if ($this->findByModuleId($moduleId) !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE module_rich_text_payloads
                 SET body_text = ?, primary_cta_label = ?, primary_cta_url = ?, secondary_cta_label = ?, secondary_cta_url = ?, updated_at = NOW()
                 WHERE module_id = ?'
            );
            $stmt->execute($params);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO module_rich_text_payloads (
                body_text, primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url, module_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute($params);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
