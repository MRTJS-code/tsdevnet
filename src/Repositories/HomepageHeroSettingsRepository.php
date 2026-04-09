<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageHeroSettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(): ?array
    {
        $stmt = $this->pdo->query('SELECT * FROM homepage_hero_settings WHERE id = 1 LIMIT 1');
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function replace(array $data): void
    {
        $params = [
            trim((string) ($data['site_title'] ?? '')),
            $this->nullableString($data['eyebrow'] ?? null),
            trim((string) ($data['title'] ?? '')),
            $this->nullableString($data['summary_text'] ?? null),
            $this->nullableString($data['supporting_text'] ?? null),
            $this->nullableString($data['profile_name'] ?? null),
            $this->nullableString($data['profile_role'] ?? null),
            $this->nullableString($data['profile_location'] ?? null),
            $this->nullableString($data['profile_availability'] ?? null),
            !empty($data['open_to_work']) ? 1 : 0,
            trim((string) ($data['cta_mode'] ?? 'register_request_chat')),
            trim((string) ($data['primary_cta_label'] ?? '')),
            trim((string) ($data['primary_cta_url'] ?? '')),
            $this->nullableString($data['secondary_cta_label'] ?? null),
            $this->nullableString($data['secondary_cta_url'] ?? null),
            !empty($data['headshot_document_id']) ? (int) $data['headshot_document_id'] : null,
        ];

        if ($this->get() !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE homepage_hero_settings SET
                    site_title = ?, eyebrow = ?, title = ?, summary_text = ?, supporting_text = ?,
                    profile_name = ?, profile_role = ?, profile_location = ?, profile_availability = ?,
                    open_to_work = ?, cta_mode = ?, primary_cta_label = ?, primary_cta_url = ?,
                    secondary_cta_label = ?, secondary_cta_url = ?, headshot_document_id = ?, updated_at = NOW()
                 WHERE id = 1'
            );
            $stmt->execute($params);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_hero_settings (
                site_title, eyebrow, title, summary_text, supporting_text,
                profile_name, profile_role, profile_location, profile_availability,
                open_to_work, cta_mode, primary_cta_label, primary_cta_url,
                secondary_cta_label, secondary_cta_url, headshot_document_id,
                id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        $stmt->execute($params);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
