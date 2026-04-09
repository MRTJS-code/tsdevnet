<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleListItemRepository
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
            'SELECT * FROM module_list_items
             WHERE module_id IN (' . $placeholders . ') AND is_active = 1
             ORDER BY module_id ASC, display_order ASC, id ASC'
        );
        $stmt->execute(array_values($moduleIds));
        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int) $row['module_id']][] = [
                'item_title' => $row['item_title'],
                'item_body' => $row['item_body'] ?? '',
                'item_meta' => $row['item_meta'] ?? '',
                'link_label' => $row['link_label'] ?? '',
                'link_url' => $row['link_url'] ?? '',
            ];
        }
        return $grouped;
    }

    public function replaceForModule(int $moduleId, array $items): void
    {
        $delete = $this->pdo->prepare('DELETE FROM module_list_items WHERE module_id = ?');
        $delete->execute([$moduleId]);
        $insert = $this->pdo->prepare(
            'INSERT INTO module_list_items (
                module_id, item_title, item_body, item_meta, link_label, link_url,
                display_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        foreach (array_values($items) as $index => $item) {
            $insert->execute([
                $moduleId,
                trim((string) ($item['item_title'] ?? '')),
                $this->nullable($item['item_body'] ?? null),
                $this->nullable($item['item_meta'] ?? null),
                $this->nullable($item['link_label'] ?? null),
                $this->nullable($item['link_url'] ?? null),
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
