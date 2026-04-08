<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HomepageExperienceHighlightRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByExperienceIds(array $experienceIds, bool $activeOnly = true): array
    {
        if ($experienceIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($experienceIds), '?'));
        $query = 'SELECT * FROM profile_experience_highlights WHERE experience_id IN (' . $placeholders . ')';
        if ($activeOnly) {
            $query .= ' AND is_active = 1';
        }
        $query .= ' ORDER BY display_order ASC, id ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($experienceIds));

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int) $row['experience_id']][] = $row;
        }

        return $grouped;
    }

    public function replaceForExperience(int $experienceId, array $highlights): void
    {
        $delete = $this->pdo->prepare('DELETE FROM profile_experience_highlights WHERE experience_id = ?');
        $delete->execute([$experienceId]);

        if ($highlights === []) {
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO profile_experience_highlights (experience_id, highlight_text, display_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())'
        );

        foreach (array_values($highlights) as $index => $highlight) {
            $text = trim((string) $highlight);
            if ($text === '') {
                continue;
            }

            $insert->execute([$experienceId, $text, ($index + 1) * 10, 1]);
        }
    }
}
