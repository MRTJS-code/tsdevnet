<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\RateLimitRepository;

final class RateLimitService
{
    public function __construct(private RateLimitRepository $rateLimits)
    {
    }

    public function attempt(string $scope, int $limit, int $windowSeconds, string $ip, ?int $userId = null): array
    {
        $windowStart = date('Y-m-d H:i:s', (int) floor(time() / $windowSeconds) * $windowSeconds);
        $count = $this->rateLimits->increment($scope, $windowStart, $ip, $userId);

        return [
            'allowed' => $count <= $limit,
            'remaining' => max(0, $limit - $count),
        ];
    }
}

