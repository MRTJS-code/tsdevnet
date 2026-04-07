<?php
declare(strict_types=1);

namespace App\Guards;

use App\Http\Response;
use App\Services\AdminAuthService;

final class AdminGuard
{
    public function __construct(private AdminAuthService $adminAuth)
    {
    }

    public function requireAdmin(): void
    {
        if (!$this->adminAuth->isAuthenticated()) {
            Response::redirect('/admin/login.php');
        }
    }
}
