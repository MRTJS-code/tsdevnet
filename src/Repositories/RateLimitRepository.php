<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

final class RateLimitRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function increment(string $scope, string $windowStart, string $ipAddress, ?int $userId): int
    {
        $query = 'SELECT id, count
                  FROM rate_limits
                  WHERE scope = ? AND window_start = ? AND ip_address = ? AND ' . ($userId === null ? 'user_id IS NULL' : 'user_id = ?') . '
                  FOR UPDATE';

        $params = [$scope, $windowStart, $ipAddress];
        if ($userId !== null) {
            $params[] = $userId;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $count = (int) $row['count'] + 1;
                $update = $this->pdo->prepare('UPDATE rate_limits SET count = ? WHERE id = ?');
                $update->execute([$count, $row['id']]);
            } else {
                $count = 1;
                $insert = $this->pdo->prepare(
                    'INSERT INTO rate_limits (user_id, ip_address, scope, window_start, count)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insert->execute([$userId, $ipAddress, $scope, $windowStart, $count]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $count;
    }
}
