<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminUserRepository;
use App\Support\Session;

final class AdminAuthService
{
    private ?array $currentAdmin = null;

    public function __construct(
        private AdminUserRepository $admins,
        private AuditService $audit
    ) {
    }

    public function isAuthenticated(): bool
    {
        $admin = $this->currentAdmin();
        return $admin !== null && !empty($admin['is_active']);
    }

    public function currentAdmin(): ?array
    {
        if ($this->currentAdmin !== null) {
            return $this->currentAdmin;
        }

        $adminId = $_SESSION['admin_user_id'] ?? null;
        if (!$adminId) {
            return null;
        }

        $this->currentAdmin = $this->admins->findById((int) $adminId);
        return $this->currentAdmin;
    }

    public function login(string $email, string $password, string $ip): bool
    {
        $admin = $this->admins->findByEmail(trim(strtolower($email)));
        if (!$admin || !(bool) $admin['is_active'] || !password_verify($password, $admin['password_hash'])) {
            $this->audit->log('admin', $admin ? (int) $admin['id'] : null, 'admin_login_failed', ['email' => $email], $ip);
            return false;
        }

        Session::regenerate();
        $_SESSION['admin_user_id'] = (int) $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_display_name'] = $admin['display_name'];
        $this->admins->updateLastLogin((int) $admin['id']);
        $this->currentAdmin = $admin;
        $this->audit->log('admin', (int) $admin['id'], 'admin_login_success', ['email' => $admin['email']], $ip);
        return true;
    }

    public function logout(string $ip): void
    {
        if ($this->isAuthenticated() && isset($_SESSION['admin_user_id'])) {
            $this->audit->log('admin', (int) $_SESSION['admin_user_id'], 'admin_logout', ['email' => $_SESSION['admin_email'] ?? ''], $ip);
        }

        unset($_SESSION['admin_user_id'], $_SESSION['admin_email'], $_SESSION['admin_display_name']);
        $this->currentAdmin = null;
        Session::regenerate();
    }
}
