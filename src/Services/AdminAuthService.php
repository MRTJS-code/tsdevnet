<?php
declare(strict_types=1);

namespace App\Services;

use App\Support\Session;

final class AdminAuthService
{
    public function __construct(
        private AuditService $audit,
        private array $config
    ) {
    }

    public function isAuthenticated(): bool
    {
        return !empty($_SESSION['admin_authenticated']);
    }

    public function login(string $username, string $password, string $ip): bool
    {
        $expectedUsername = (string) ($this->config['admin']['username'] ?? '');
        $passwordHash = (string) ($this->config['admin']['password_hash'] ?? '');

        $valid = hash_equals($expectedUsername, $username) && $passwordHash !== '' && password_verify($password, $passwordHash);
        if ($valid) {
            Session::regenerate();
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_username'] = $username;
            $this->audit->log('admin', null, 'admin_login_success', ['username' => $username], $ip);
            return true;
        }

        $this->audit->log('admin', null, 'admin_login_failed', ['username' => $username], $ip);
        return false;
    }

    public function logout(string $ip): void
    {
        if ($this->isAuthenticated()) {
            $this->audit->log('admin', null, 'admin_logout', ['username' => $_SESSION['admin_username'] ?? ''], $ip);
        }

        unset($_SESSION['admin_authenticated'], $_SESSION['admin_username']);
        Session::regenerate();
    }
}

