<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SiteSettingRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM site_settings ORDER BY setting_key ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllIndexed(): array
    {
        $settings = [];
        foreach ($this->listAll() as $row) {
            $settings[$row['setting_key']] = $row;
        }

        return $settings;
    }

    public function upsert(string $settingKey, ?string $value, string $valueType = 'string'): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value_text, value_type, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE setting_value_text = VALUES(setting_value_text), value_type = VALUES(value_type), updated_at = NOW()'
        );
        $stmt->execute([$settingKey, $value, $valueType]);
    }

    public function upsertMany(array $settings): void
    {
        foreach ($settings as $settingKey => $data) {
            $this->upsert(
                (string) $settingKey,
                isset($data['value']) ? (string) $data['value'] : null,
                (string) ($data['type'] ?? 'string')
            );
        }
    }
}
