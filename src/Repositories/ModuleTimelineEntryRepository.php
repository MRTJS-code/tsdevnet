<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ModuleTimelineEntryRepository
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
            'SELECT * FROM module_timeline_entries
             WHERE module_id IN (' . $placeholders . ') AND is_active = 1
             ORDER BY module_id ASC, display_order ASC, id ASC'
        );
        $stmt->execute(array_values($moduleIds));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entryIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $highlightsByEntryId = $this->highlightsByEntryIds($entryIds);
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row['module_id']][] = [
                'id' => (int) $row['id'],
                'title' => $row['entry_title'],
                'subtitle' => $row['entry_subtitle'] ?? '',
                'meta' => $row['meta_text'] ?? '',
                'summary_text' => $row['summary_text'] ?? '',
                'detail_text' => $row['detail_text'] ?? '',
                'highlights' => $highlightsByEntryId[(int) $row['id']] ?? [],
            ];
        }

        return $grouped;
    }

    public function replaceForModule(int $moduleId, array $entries): void
    {
        $delete = $this->pdo->prepare('DELETE FROM module_timeline_entries WHERE module_id = ?');
        $delete->execute([$moduleId]);

        $insertEntry = $this->pdo->prepare(
            'INSERT INTO module_timeline_entries (
                module_id, entry_title, entry_subtitle, meta_text, summary_text, detail_text,
                display_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $insertHighlight = $this->pdo->prepare(
            'INSERT INTO module_timeline_highlights (
                timeline_entry_id, highlight_text, display_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, 1, NOW(), NOW())'
        );

        foreach (array_values($entries) as $index => $entry) {
            $insertEntry->execute([
                $moduleId,
                trim((string) ($entry['title'] ?? '')),
                $this->nullableString($entry['subtitle'] ?? null),
                $this->nullableString($entry['meta'] ?? null),
                $this->nullableString($entry['summary_text'] ?? null),
                $this->nullableString($entry['detail_text'] ?? null),
                (int) ($entry['display_order'] ?? (($index + 1) * 10)),
                !array_key_exists('is_active', $entry) || !empty($entry['is_active']) ? 1 : 0,
            ]);
            $entryId = (int) $this->pdo->lastInsertId();

            foreach (array_values($entry['highlights'] ?? []) as $highlightIndex => $highlight) {
                $text = trim((string) $highlight);
                if ($text === '') {
                    continue;
                }

                $insertHighlight->execute([
                    $entryId,
                    $text,
                    ($highlightIndex + 1) * 10,
                ]);
            }
        }
    }

    private function highlightsByEntryIds(array $entryIds): array
    {
        if ($entryIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($entryIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT * FROM module_timeline_highlights
             WHERE timeline_entry_id IN (' . $placeholders . ') AND is_active = 1
             ORDER BY timeline_entry_id ASC, display_order ASC, id ASC'
        );
        $stmt->execute(array_values($entryIds));
        $grouped = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int) $row['timeline_entry_id']][] = $row['highlight_text'];
        }

        return $grouped;
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
