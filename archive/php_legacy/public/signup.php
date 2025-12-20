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
