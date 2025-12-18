<?php
declare(strict_types=1);

class RateLimiter
{
    private PDO $pdo;
    private ?int $userId;
    private string $ip;

    public function __construct(PDO $pdo, ?int $userId, string $ip)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->ip = $ip;
    }

    /**
     * @return array{allowed:bool, remaining:int}
     */
    public function attempt(string $scope, int $limit, int $windowSeconds): array
    {
        $windowStart = date('Y-m-d H:i:s', floor(time() / $windowSeconds) * $windowSeconds);
        $stmt = $this->pdo->prepare(
            'SELECT id, count FROM rate_limits WHERE scope = ? AND window_start = ? AND ip_address = ? AND ' .
            ($this->userId ? 'user_id = ?' : 'user_id IS NULL') . ' FOR UPDATE'
        );
        $params = [$scope, $windowStart, $this->ip];
        if ($this->userId) {
            $params[] = $this->userId;
        }
        $this->pdo->beginTransaction();
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row) {
            $count = (int)$row['count'] + 1;
            $upd = $this->pdo->prepare('UPDATE rate_limits SET count = ? WHERE id = ?');
            $upd->execute([$count, $row['id']]);
        } else {
            $count = 1;
            $ins = $this->pdo->prepare(
                'INSERT INTO rate_limits (user_id, ip_address, scope, window_start, count) VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$this->userId, $this->ip, $scope, $windowStart, $count]);
        }
        $this->pdo->commit();

        $allowed = $count <= $limit;
        $remaining = max(0, $limit - $count);
        return ['allowed' => $allowed, 'remaining' => $remaining];
    }
}
