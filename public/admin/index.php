<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$config = $app['config'];
$approval = $app['services']['approval'];
$admin = $app['services']['admin_auth']->currentAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($config);

    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        echo 'Invalid CSRF token.';
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');
    $userId = (int) ($_POST['user_id'] ?? 0);
    $statusMap = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'block' => 'blocked',
    ];

    if ($userId > 0 && isset($statusMap[$action])) {
        $approval->changeStatus($userId, $statusMap[$action], Util::clientIp());
    }
}

View::render('admin/dashboard', [
    'title' => 'Admin | ' . $config['app_name'],
    'bodyClass' => 'page',
    'pendingUsers' => $approval->pendingUsers(),
    'admin' => $admin,
]);
