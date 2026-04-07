<?php
declare(strict_types=1);

use App\Support\Security;
use App\Support\Turnstile;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../src/bootstrap.php';

$config = $app['config'];
$userService = $app['services']['user'];
$rateLimits = $app['services']['rate_limits'];

$errors = [];
$success = false;
$magicLink = null;
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($config);

    $email = Util::sanitizeEmail((string) ($_POST['email'] ?? ''));
    $turnstileToken = (string) ($_POST['cf-turnstile-response'] ?? '');

    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    }

    $rate = $rateLimits->attempt('login:' . date('Y-m-d-H'), (int) $config['rate_limits']['login_per_hour'], 3600, Util::clientIp());
    if (!$rate['allowed']) {
        $errors[] = 'Too many login link requests from this address. Please try again later.';
    }

    if (!Util::validateEmail($email)) {
        $errors[] = 'Enter a valid email address.';
    }

    $turnstilePassed = (Util::isDev($config) && empty($config['turnstile']['secret_key']))
        || Turnstile::verify((string) ($config['turnstile']['secret_key'] ?? ''), $turnstileToken, Util::clientIp());
    if (!$turnstilePassed) {
        $errors[] = 'CAPTCHA verification failed. Please try again.';
    }

    if (!$errors) {
        $magicLink = $userService->requestLoginLink($email, Util::clientIp(), Util::userAgent());
        $success = true;
        $old = [];
    }
}

View::render('auth/login', [
    'title' => 'Portal login | ' . $config['app_name'],
    'bodyClass' => 'page',
    'turnstileEnabled' => true,
    'config' => $config,
    'errors' => $errors,
    'success' => $success,
    'magicLink' => $magicLink,
    'old' => $old,
]);
