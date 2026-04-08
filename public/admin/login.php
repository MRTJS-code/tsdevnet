<?php
declare(strict_types=1);

use App\Http\Response;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

$config = $app['config'];
$errors = [];
$old = $_POST;
$adminCount = $app['repositories']['admin_users']->countActive();

if ($app['services']['admin_auth']->isAuthenticated()) {
    Response::redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($config);

    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    }

    $rate = $app['services']['rate_limits']->attempt(
        'admin-login:' . date('Y-m-d-H'),
        (int) $config['rate_limits']['admin_login_per_hour'],
        3600,
        Util::clientIp()
    );
    if (!$rate['allowed']) {
        $errors[] = 'Too many admin login attempts. Please try again later.';
    }

    if (!$errors) {
        $ok = $app['services']['admin_auth']->login(
            trim((string) ($_POST['email'] ?? '')),
            (string) ($_POST['password'] ?? ''),
            Util::clientIp()
        );

        if ($ok) {
            Response::redirect('/admin/index.php');
        }

        $errors[] = 'Invalid admin credentials.';
    }
}

View::render('admin/login', [
    'title' => 'Admin login | ' . $config['app_name'],
    'bodyClass' => 'page',
    'errors' => $errors,
    'old' => $old,
    'adminCount' => $adminCount,
]);
