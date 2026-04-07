<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Support\Session;

final class AuthService
{
    private ?array $currentUser = null;

    public function __construct(
        private UserRepository $users,
        private AuditService $audit
    ) {
    }

    public function currentUser(): ?array
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $this->currentUser = $this->users->findById((int) $userId);
        return $this->currentUser;
    }

    public function loginUser(int $userId, string $ip): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = $userId;
        unset($_SESSION['conversation_id']);
        $this->currentUser = $this->users->findById($userId);
        $this->audit->log('user', $userId, 'user_session_started', [], $ip);
    }

    public function logout(string $ip): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->audit->log('user', (int) $userId, 'user_session_ended', [], $ip);
        }

        Session::destroy();
        $this->currentUser = null;
    }
}

