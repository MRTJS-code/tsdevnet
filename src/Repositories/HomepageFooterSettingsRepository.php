<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageFooterSettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(): ?array
    {
        $stmt = $this->pdo->query('SELECT * FROM homepage_footer_settings WHERE id = 1 LIMIT 1');
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function replace(array $data): void
    {
        $params = [
            $this->nullableString($data['heading'] ?? null),
            $this->nullableString($data['body_text'] ?? null),
            $this->nullableString($data['contact_email'] ?? null),
            $this->nullableString($data['contact_phone'] ?? null),
            $this->nullableString($data['contact_location'] ?? null),
            !empty($data['cv_document_id']) ? (int) $data['cv_document_id'] : null,
            $this->nullableString($data['linkedin_url'] ?? null),
            $this->nullableString($data['github_url'] ?? null),
        ];

        if ($this->get() !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE homepage_footer_settings SET
                    heading = ?, body_text = ?, contact_email = ?, contact_phone = ?, contact_location = ?,
                    cv_document_id = ?, linkedin_url = ?, github_url = ?, updated_at = NOW()
                 WHERE id = 1'
            );
            $stmt->execute($params);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_footer_settings (
                heading, body_text, contact_email, contact_phone, contact_location,
                cv_document_id, linkedin_url, github_url, id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        $stmt->execute($params);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
