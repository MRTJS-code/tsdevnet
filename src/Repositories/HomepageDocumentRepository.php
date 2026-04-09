<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageDocumentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(bool $activeOnly = false): array
    {
        $query = 'SELECT * FROM documents';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return array_map(fn (array $row): array => $this->mapRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM documents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function findByDocumentKey(string $documentKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM documents WHERE document_key = ? LIMIT 1');
        $stmt->execute([$this->canonicalKey($documentKey)]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ? $this->mapRow($record) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO documents (document_key, document_type, label, description_text, file_path, external_url, mime_type, file_size_bytes, is_public, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $this->canonicalKey($data['document_key']),
            $this->documentType($data),
            $data['title'] ?? $data['label'],
            ($data['description_text'] ?? '') ?: null,
            ($data['file_path'] ?? '') ?: null,
            ($data['external_url'] ?? '') ?: null,
            ($data['mime_type'] ?? '') ?: null,
            !empty($data['file_size_bytes']) ? (int) $data['file_size_bytes'] : null,
            !empty($data['is_public']) ? 1 : (!empty($data['file_path']) || !empty($data['external_url']) ? 1 : 0),
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE documents
             SET document_key = ?, document_type = ?, label = ?, description_text = ?, file_path = ?, external_url = ?, mime_type = ?, file_size_bytes = ?, is_public = ?, display_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $this->canonicalKey($data['document_key']),
            $this->documentType($data),
            $data['title'] ?? $data['label'],
            ($data['description_text'] ?? '') ?: null,
            ($data['file_path'] ?? '') ?: null,
            ($data['external_url'] ?? '') ?: null,
            ($data['mime_type'] ?? '') ?: null,
            !empty($data['file_size_bytes']) ? (int) $data['file_size_bytes'] : null,
            !empty($data['is_public']) ? 1 : (!empty($data['file_path']) || !empty($data['external_url']) ? 1 : 0),
            (int) ($data['sort_order'] ?? $data['display_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function upsertByDocumentKey(array $data): int
    {
        $existing = $this->findByDocumentKey($data['document_key']);
        if ($existing) {
            $this->update((int) $existing['id'], $data);
            return (int) $existing['id'];
        }

        return $this->create($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM documents WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function mapRow(array $row): array
    {
        $legacyKey = $this->legacyKey($row['document_key']);

        return $row + [
            'document_key' => $legacyKey,
            'document_type' => $row['document_type'] ?? ($legacyKey === 'hero_headshot' ? 'headshot' : 'cv_pdf'),
            'title' => $row['label'],
            'sort_order' => (int) ($row['display_order'] ?? 0),
            'file_size_bytes' => isset($row['file_size_bytes']) ? (int) $row['file_size_bytes'] : null,
            'external_url' => $row['external_url'] ?? '',
        ];
    }

    private function canonicalKey(string $documentKey): string
    {
        return match ($documentKey) {
            'hero_headshot' => 'headshot',
            'footer_cv' => 'cv',
            default => $documentKey,
        };
    }

    private function legacyKey(string $documentKey): string
    {
        return match ($documentKey) {
            'headshot' => 'hero_headshot',
            'cv' => 'footer_cv',
            default => $documentKey,
        };
    }

    private function documentType(array $data): string
    {
        $type = trim((string) ($data['document_type'] ?? 'file'));
        return $type !== '' ? $type : 'file';
    }
}
