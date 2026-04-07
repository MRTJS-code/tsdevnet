<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;

final class AuditService
{
    public function __construct(private AuditLogRepository $auditLogs)
    {
    }

    public function log(string $actorType, ?int $actorId, string $action, array $metadata, string $ip): void
    {
        $this->auditLogs->log($actorType, $actorId, $action, $metadata, $ip);
    }
}

