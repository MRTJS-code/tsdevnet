<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleQuoteCardItemRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listGroupedByModuleIds(array $moduleIds): array
    {
        if ($moduleIds === []) {
            return [];
        }
        $placeholders = implode(', ', array_fill(0, count($moduleIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT * FROM module_quote_card_items
             WHERE module_id IN (' . $placeholders . ') AND is_active = 1
             ORDER BY module_id ASC, display_order ASC, id ASC'
        );
        $stmt->execute(array_values($moduleIds));
        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int) $row['module_id']][] = [
                'quote_text' => $row['quote_text'],
                'attribution_name' => $row['attribution_name'],
                'attribution_role' => $row['attribution_role'] ?? '',
                'attribution_context' => $row['attribution_context'] ?? '',
            ];
        }
        return $grouped;
    }

    public function replaceForModule(int $moduleId, array $items): void
    {
        $delete = $this->pdo->prepare('DELETE FROM module_quote_card_items WHERE module_id = ?');
        $delete->execute([$moduleId]);
        $insert = $this->pdo->prepare(
            'INSERT INTO module_quote_card_items (
                module_id, quote_text, attribution_name, attribution_role, attribution_context,
                display_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        foreach (array_values($items) as $index => $item) {
            $insert->execute([
                $moduleId,
                trim((string) ($item['quote_text'] ?? '')),
                trim((string) ($item['attribution_name'] ?? '')),
                $this->nullable($item['attribution_role'] ?? null),
                $this->nullable($item['attribution_context'] ?? null),
                (int) ($item['display_order'] ?? (($index + 1) * 10)),
                !array_key_exists('is_active', $item) || !empty($item['is_active']) ? 1 : 0,
            ]);
        }
    }

    private function nullable(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
