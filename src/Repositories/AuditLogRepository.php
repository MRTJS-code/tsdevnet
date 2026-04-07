<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuditLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function log(string $actorType, ?int $actorId, string $action, array $metadata, string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_log (actor_type, actor_id, action, metadata, created_at, ip_address)
             VALUES (?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([
            $actorType,
            $actorId,
            $action,
            json_encode($metadata, JSON_UNESCAPED_SLASHES),
            $ip,
        ]);
    }
}

