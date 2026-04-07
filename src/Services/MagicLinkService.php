<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use PDO;
use Throwable;

final class MagicLinkService
{
    public function __construct(
        private PDO $pdo,
        private TokenRepository $tokens,
        private UserRepository $users,
        private AuditService $audit,
        private array $config
    ) {
    }

    public function issueToken(int $userId, string $ip, string $userAgent): array
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $ttl = (int) ($this->config['magic_link_ttl'] ?? 900);
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        $this->tokens->create($userId, $tokenHash, $expiresAt, $ip, $userAgent);
        $this->audit->log('user', $userId, 'magic_link_issued', ['expires_at' => $expiresAt], $ip);

        return [
            'token' => $token,
            'expires_at' => $expiresAt,
            'ttl' => $ttl,
        ];
    }

    public function consumeToken(string $token, string $ip): ?array
    {
        $record = $this->tokens->findActiveByHash(hash('sha256', $token));
        if (!$record || $record['used_at'] !== null || strtotime($record['expires_at']) < time() || in_array($record['status'], ['blocked', 'rejected'], true)) {
            return null;
        }

        $this->pdo->beginTransaction();

        try {
            $this->tokens->markUsed((int) $record['id']);
            $this->users->touchLastLogin((int) $record['user_id']);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        $this->audit->log('user', (int) $record['user_id'], 'magic_link_consumed', ['email' => $record['email']], $ip);

        return [
            'id' => (int) $record['user_id'],
            'email' => $record['email'],
            'name' => $record['name'],
            'status' => $record['status'],
        ];
    }
}
