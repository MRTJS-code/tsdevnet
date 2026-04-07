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
    $name = trim($_POST['name'] ?? '');
    $email = Util::sanitizeEmail($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $roleType = trim($_POST['role_type'] ?? '');
    $linkedin = trim($_POST['linkedin_url'] ?? '');
    $hiringFor = trim($_POST['hiring_for'] ?? '');
    $consent = isset($_POST['consent']);
    $cfToken = $_POST['cf-turnstile-response'] ?? '';

    if (!Util::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    }
    if (!$consent) {
        $errors[] = 'Consent is required.';
    }
    if (!$name || !$company || !$roleType) {
        $errors[] = 'All required fields must be filled.';
    }
    if (!Util::validateEmail($email)) {
        $errors[] = 'Enter a valid email.';
    }
    if (!Turnstile::verify($config['turnstile']['secret_key'] ?? '', $cfToken, Util::clientIp())) {
        $errors[] = 'CAPTCHA failed. Please try again.';
    }

    if (!$errors) {
        $userId = $auth->createUser([
            'name' => $name,
            'email' => $email,
            'company' => $company,
            'role_type' => $roleType,
            'linkedin_url' => $linkedin,
            'hiring_for' => $hiringFor,
            'consent_at' => date('Y-m-d H:i:s'),
        ]);

        $tokenData = $auth->createMagicToken($userId, Util::clientIp(), Util::userAgent());
        $link = Util::baseUrl($config) . '/verify.php?token=' . urlencode($tokenData['token']);

        $mailer->sendMagicLink($email, $name, $link, $tokenData['ttl']);
        if (Util::isDev($config)) {
            $magicLink = $link;
        }

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request access</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="page">
<main class="form-page">
    <h1>Request access</h1>
    <?php if ($success): ?>
        <div class="notice success">
            <p>Check your email for a secure login link (valid 15 minutes). Pending users get demo access while awaiting approval.</p>
            <?php if ($magicLink): ?>
                <p><strong>DEV link:</strong> <a href="<?php echo Util::e($magicLink); ?>"><?php echo Util::e($magicLink); ?></a></p>
            <?php endif; ?>
            <p><a href="/login.php" class="btn">Back to login</a></p>
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
            <label>Name* <input type="text" name="name" required value="<?php echo Util::e($_POST['name'] ?? ''); ?>"></label>
            <label>Email* <input type="email" name="email" required value="<?php echo Util::e($_POST['email'] ?? ''); ?>"></label>
            <label>Company* <input type="text" name="company" required value="<?php echo Util::e($_POST['company'] ?? ''); ?>"></label>
            <label>Role type*
                <select name="role_type" required>
                    <option value="">Select one</option>
                    <?php
                    $roles = ['Recruiter', 'Hiring Manager', 'Other'];
                    $selectedRole = $_POST['role_type'] ?? '';
                    foreach ($roles as $role) {
                        $sel = $selectedRole === $role ? 'selected' : '';
                        echo "<option value=\"" . Util::e($role) . "\" {$sel}>" . Util::e($role) . "</option>";
                    }
                    ?>
                </select>
            </label>
            <label>LinkedIn URL <input type="url" name="linkedin_url" value="<?php echo Util::e($_POST['linkedin_url'] ?? ''); ?>"></label>
            <label>What roles are you hiring for? <textarea name="hiring_for"><?php echo Util::e($_POST['hiring_for'] ?? ''); ?></textarea></label>
            <label class="checkbox">
                <input type="checkbox" name="consent" value="1" <?php echo isset($_POST['consent']) ? 'checked' : ''; ?> required>
                I consent to be contacted about this request.
            </label>
            <div class="cf-turnstile" data-sitekey="<?php echo Util::e($config['turnstile']['site_key'] ?? ''); ?>"></div>
            <button type="submit" class="btn primary">Submit</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
