<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleCtaBannerPayloadRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByModuleId(int $moduleId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM module_cta_banner_payloads WHERE module_id = ? LIMIT 1');
        $stmt->execute([$moduleId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function upsertForModule(int $moduleId, array $data): void
    {
        $params = [
            $this->nullable($data['body_text'] ?? null),
            $this->nullable($data['primary_cta_label'] ?? null),
            $this->nullable($data['primary_cta_url'] ?? null),
            $this->nullable($data['secondary_cta_label'] ?? null),
            $this->nullable($data['secondary_cta_url'] ?? null),
            $moduleId,
        ];

        if ($this->findByModuleId($moduleId) !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE module_cta_banner_payloads
                 SET body_text = ?, primary_cta_label = ?, primary_cta_url = ?, secondary_cta_label = ?, secondary_cta_url = ?, updated_at = NOW()
                 WHERE module_id = ?'
            );
            $stmt->execute($params);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO module_cta_banner_payloads (
                body_text, primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url, module_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute($params);
    }

    private function nullable(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
