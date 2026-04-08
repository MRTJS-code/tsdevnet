<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AssistantKnowledgeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM assistant_knowledge ORDER BY priority DESC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM assistant_knowledge WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function listActiveForTier(string $tier): array
    {
        $allowedTiers = $tier === 'approved' ? ['pending', 'approved'] : ['pending'];
        $placeholders = implode(', ', array_fill(0, count($allowedTiers), '?'));

        $stmt = $this->pdo->prepare(
            'SELECT * FROM assistant_knowledge
             WHERE is_active = 1 AND minimum_access_tier IN (' . $placeholders . ')
             ORDER BY priority DESC, id ASC'
        );
        $stmt->execute($allowedTiers);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO assistant_knowledge (knowledge_key, trigger_type, trigger_value, response_text, minimum_access_tier, priority, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['knowledge_key'],
            $data['trigger_type'],
            $data['trigger_value'],
            $data['response_text'],
            $data['minimum_access_tier'],
            (int) ($data['priority'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE assistant_knowledge
             SET knowledge_key = ?, trigger_type = ?, trigger_value = ?, response_text = ?, minimum_access_tier = ?, priority = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['knowledge_key'],
            $data['trigger_type'],
            $data['trigger_value'],
            $data['response_text'],
            $data['minimum_access_tier'],
            (int) ($data['priority'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }
}

