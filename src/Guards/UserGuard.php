<?php
declare(strict_types=1);

namespace App\Guards;

use App\Http\Response;
use App\Services\AuthService;

final class UserGuard
{
    public function __construct(private AuthService $auth)
    {
    }

    public function requireUser(): array
    {
        $user = $this->auth->currentUser();
        if (!$user) {
            Response::redirect('/login.php');
        }

        return $user;
    }
}

