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
        $query = 'SELECT * FROM homepage_documents';
        if ($activeOnly) {
            $query .= ' WHERE is_active = 1';
        }
        $query .= ' ORDER BY sort_order ASC, id ASC';

        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_documents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function findByDocumentKey(string $documentKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM homepage_documents WHERE document_key = ? LIMIT 1');
        $stmt->execute([$documentKey]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO homepage_documents (document_key, document_type, title, description_text, file_path, external_url, mime_type, file_size_bytes, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['document_key'],
            $data['document_type'],
            $data['title'],
            $data['description_text'] ?: null,
            $data['file_path'] ?: null,
            $data['external_url'] ?: null,
            $data['mime_type'] ?: null,
            $data['file_size_bytes'] ? (int) $data['file_size_bytes'] : null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE homepage_documents
             SET document_key = ?, document_type = ?, title = ?, description_text = ?, file_path = ?, external_url = ?, mime_type = ?, file_size_bytes = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['document_key'],
            $data['document_type'],
            $data['title'],
            $data['description_text'] ?: null,
            $data['file_path'] ?: null,
            $data['external_url'] ?: null,
            $data['mime_type'] ?: null,
            $data['file_size_bytes'] ? (int) $data['file_size_bytes'] : null,
            (int) ($data['sort_order'] ?? 0),
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
}
