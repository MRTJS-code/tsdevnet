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
$userId = (int) ($_GET['id'] ?? 0);

if ($userId <= 0) {
    http_response_code(404);
    echo 'User not found.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($config);

    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        echo 'Invalid CSRF token.';
        exit;
    }

    $approval->updateNotes($userId, trim((string) ($_POST['admin_notes'] ?? '')), Util::clientIp());
}

$detail = $approval->userDetail($userId);
if (!$detail) {
    http_response_code(404);
    echo 'User not found.';
    exit;
}

View::render('admin/user', [
    'title' => 'Admin user review | ' . $config['app_name'],
    'bodyClass' => 'page',
    'detail' => $detail,
]);
