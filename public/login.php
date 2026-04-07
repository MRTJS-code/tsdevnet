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

return;
?>

<?php
require __DIR__ . '/../src/bootstrap.php';

$pdo = Db::get($config);
$auth = new Auth($pdo, $config);
$mailer = new Mailer($config);

$errors = [];
$success = false;
$magicLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = Util::sanitizeEmail($_POST['email'] ?? '');
    $cfToken = $_POST['cf-turnstile-response'] ?? '';

    if (!Util::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    }
    if (!Util::validateEmail($email)) {
        $errors[] = 'Enter a valid email.';
    }
    if (!Turnstile::verify($config['turnstile']['secret_key'] ?? '', $cfToken, Util::clientIp())) {
        $errors[] = 'CAPTCHA failed. Please try again.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, name, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] !== 'blocked') {
            $tokenData = $auth->createMagicToken((int)$user['id'], Util::clientIp(), Util::userAgent());
            $link = Util::baseUrl($config) . '/verify.php?token=' . urlencode($tokenData['token']);
            $mailer->sendMagicLink($email, $user['name'], $link, $tokenData['ttl']);
            if (Util::isDev($config)) {
                $magicLink = $link;
            }
        }
        // Generic response regardless of existence
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="page">
<main class="form-page">
    <h1>Log in</h1>
    <?php if ($success): ?>
        <div class="notice success">
            <p>If an account exists, you’ll receive a secure login link (valid 15 minutes).</p>
            <p>Need access? <a href="/signup.php">Request access</a>.</p>
            <?php if ($magicLink): ?>
                <p><strong>DEV link:</strong> <a href="<?php echo Util::e($magicLink); ?>"><?php echo Util::e($magicLink); ?></a></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php if ($errors): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo Util::e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="card form">
            <input type="hidden" name="csrf_token" value="<?php echo Util::e(Util::csrfToken()); ?>">
            <label>Email* <input type="email" name="email" required value="<?php echo Util::e($_POST['email'] ?? ''); ?>"></label>
            <div class="cf-turnstile" data-sitekey="<?php echo Util::e($config['turnstile']['site_key'] ?? ''); ?>"></div>
            <button type="submit" class="btn primary">Send login link</button>
            <p class="help-text">No passwords. We’ll email you a one-time link.</p>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
