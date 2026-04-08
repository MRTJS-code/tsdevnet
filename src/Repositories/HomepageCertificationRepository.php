<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageCertificationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM profile_certifications';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profile_certifications WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO profile_certifications (name, issuer, issued_year, status_text, credential_url, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['certification_name'] ?? $data['name'],
            ($data['issuer'] ?? '') ?: null,
            $this->normalizeIssuedYear($data['issued_label'] ?? $data['issued_year'] ?? null),
            ($data['status_text'] ?? '') ?: null,
            ($data['credential_url'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE profile_certifications
             SET name = ?, issuer = ?, issued_year = ?, status_text = ?, credential_url = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['certification_name'] ?? $data['name'],
            ($data['issuer'] ?? '') ?: null,
            $this->normalizeIssuedYear($data['issued_label'] ?? $data['issued_year'] ?? null),
            ($data['status_text'] ?? '') ?: null,
            ($data['credential_url'] ?? '') ?: null,
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM profile_certifications WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function mapRow(array $row): array
    {
        return $row + [
            'certification_name' => $row['name'],
            'issued_label' => isset($row['issued_year']) && $row['issued_year'] !== null ? (string) $row['issued_year'] : ($row['status_text'] ?? ''),
            'sort_order' => (int) ($row['display_order'] ?? 0),
        ];
    }

    private function normalizeIssuedYear(mixed $value): ?int
    {
        $text = trim((string) $value);
        if ($text === '' || !preg_match('/\d{4}/', $text, $match)) {
            return null;
        }

        return (int) $match[0];
    }
}
