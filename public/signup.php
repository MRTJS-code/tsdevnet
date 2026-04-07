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

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = Util::sanitizeEmail((string) ($_POST['email'] ?? ''));
    $company = trim((string) ($_POST['company'] ?? ''));
    $roleType = trim((string) ($_POST['role_type'] ?? ''));
    $linkedinUrl = trim((string) ($_POST['linkedin_url'] ?? ''));
    $hiringFor = trim((string) ($_POST['hiring_for'] ?? ''));
    $consent = !empty($_POST['consent']);
    $turnstileToken = (string) ($_POST['cf-turnstile-response'] ?? '');

    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    }

    $rate = $rateLimits->attempt('signup:' . date('Y-m-d-H'), (int) $config['rate_limits']['signup_per_hour'], 3600, Util::clientIp());
    if (!$rate['allowed']) {
        $errors[] = 'Too many access requests from this address. Please try again later.';
    }

    if ($name === '' || $company === '' || $roleType === '') {
        $errors[] = 'Please complete the required fields.';
    }
    if (!$consent) {
        $errors[] = 'Consent is required.';
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
        $result = $userService->createOrReuseAccessRequest([
            'name' => $name,
            'email' => $email,
            'company' => $company,
            'role_type' => $roleType,
            'linkedin_url' => $linkedinUrl,
            'hiring_for' => $hiringFor,
        ], Util::clientIp(), Util::userAgent());

        $magicLink = $result['magic_link'];
        $success = true;
        $old = [];
    }
}

View::render('auth/signup', [
    'title' => 'Request access | ' . $config['app_name'],
    'bodyClass' => 'page',
    'turnstileEnabled' => true,
    'config' => $config,
    'errors' => $errors,
    'success' => $success,
    'magicLink' => $magicLink,
    'old' => $old,
]);
