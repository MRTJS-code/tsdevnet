<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SiteSettingRepository
{
    private const COLUMN_MAP = [
        'site_title' => 'site_title',
        'hero_eyebrow' => 'hero_eyebrow',
        'hero_title' => 'hero_headline',
        'hero_headline' => 'hero_headline',
        'hero_summary' => 'hero_subheadline',
        'hero_subheadline' => 'hero_subheadline',
        'hero_supporting_text' => 'hero_supporting_text',
        'profile_name' => 'profile_name',
        'profile_role' => 'profile_role',
        'profile_location' => 'profile_location',
        'profile_availability' => 'profile_availability',
        'open_to_work' => 'open_to_work',
        'cta_mode' => 'primary_cta_mode',
        'primary_cta_mode' => 'primary_cta_mode',
        'cta_primary_label' => 'primary_cta_label',
        'primary_cta_label' => 'primary_cta_label',
        'cta_primary_url' => 'primary_cta_url',
        'primary_cta_url' => 'primary_cta_url',
        'cta_secondary_label' => 'secondary_cta_label',
        'secondary_cta_label' => 'secondary_cta_label',
        'cta_secondary_url' => 'secondary_cta_url',
        'secondary_cta_url' => 'secondary_cta_url',
        'linkedin_url' => 'linkedin_url',
        'github_url' => 'github_url',
        'contact_email' => 'contact_email',
        'contact_phone' => 'contact_phone',
        'contact_location' => 'contact_location',
        'footer_heading' => 'footer_heading',
        'footer_body' => 'footer_body',
        'chatbot_teaser_enabled' => 'chatbot_teaser_enabled',
        'chatbot_teaser_label' => 'chatbot_teaser_label',
        'headshot_document_id' => 'headshot_document_id',
        'cv_document_id' => 'cv_document_id',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $row = $this->getSingleton();
        if ($row === null) {
            return [];
        }

        $settings = [];
        foreach (self::COLUMN_MAP as $settingKey => $column) {
            if (!array_key_exists($column, $row)) {
                continue;
            }

            $settings[] = [
                'setting_key' => $settingKey,
                'setting_value_text' => $this->toStringValue($row[$column]),
                'value_type' => is_bool($row[$column]) ? 'bool' : 'string',
            ];
        }

        return $settings;
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
        $this->upsertMany([
            $settingKey => ['value' => $value, 'type' => $valueType],
        ]);
    }

    public function upsertMany(array $settings): void
    {
        $columns = [];
        $values = [];
        foreach ($settings as $settingKey => $data) {
            $column = self::COLUMN_MAP[(string) $settingKey] ?? null;
            if ($column === null) {
                continue;
            }

            $columns[$column] = $column;
            $values[$column] = $this->normalizeValue($data['value'] ?? null, (string) ($data['type'] ?? 'string'));
        }

        if ($columns === []) {
            return;
        }

        $columnNames = implode(', ', array_merge(['id'], array_values($columns)));
        $placeholders = implode(', ', array_fill(0, count($columns) + 1, '?'));
        $updateClauses = implode(', ', array_map(static fn (string $column): string => $column . ' = VALUES(' . $column . ')', array_values($columns)));

        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (' . $columnNames . ', created_at, updated_at)
             VALUES (' . $placeholders . ', NOW(), NOW())
             ON DUPLICATE KEY UPDATE ' . $updateClauses . ', updated_at = NOW()'
        );
        $stmt->execute(array_merge([1], array_values($values)));
    }

    public function getSingleton(): ?array
    {
        $stmt = $this->pdo->query('SELECT * FROM site_settings WHERE id = 1 LIMIT 1');
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function replaceSingleton(array $data): void
    {
        $columns = ['id'];
        $placeholders = ['?'];
        $updates = [];
        $values = [1];

        foreach ($data as $column => $value) {
            if (!in_array($column, array_values(self::COLUMN_MAP), true)) {
                continue;
            }

            $columns[] = $column;
            $placeholders[] = '?';
            $updates[] = $column . ' = VALUES(' . $column . ')';
            $values[] = $value;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (' . implode(', ', $columns) . ', created_at, updated_at)
             VALUES (' . implode(', ', $placeholders) . ', NOW(), NOW())
             ON DUPLICATE KEY UPDATE ' . implode(', ', $updates) . ', updated_at = NOW()'
        );
        $stmt->execute($values);
    }

    private function normalizeValue(mixed $value, string $type): mixed
    {
        if ($type === 'bool') {
            return !empty($value) ? 1 : 0;
        }

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function toStringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}
