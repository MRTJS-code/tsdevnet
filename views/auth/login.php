<?php
use App\Support\Security;
use App\Support\Util;
?>
<main class="form-page">
    <div class="form-page__header">
        <p class="eyebrow">Magic Link Login</p>
        <h1>Access the recruiter portal</h1>
        <p class="lede">No passwords are stored for recruiters. If an account exists, a one-time login link is sent to the email address.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="notice success">
            <p>If an account exists and is not blocked, a secure login link has been sent.</p>
            <?php if (!empty($magicLink)): ?>
                <p><strong>Dev link:</strong> <a href="<?= Util::e($magicLink) ?>"><?= Util::e($magicLink) ?></a></p>
            <?php endif; ?>
            <p><a href="/signup.php">Need access first?</a></p>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="notice error">
                <?php foreach ($errors as $error): ?>
                    <p><?= Util::e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="card form">
            <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
            <label>Email*
                <input type="email" name="email" required value="<?= Util::e($old['email'] ?? '') ?>">
            </label>
            <div class="cf-turnstile" data-sitekey="<?= Util::e($config['turnstile']['site_key'] ?? '') ?>"></div>
            <button type="submit" class="btn primary">Send login link</button>
            <p class="help-text">Responses stay generic to avoid account enumeration.</p>
        </form>
    <?php endif; ?>
</main>

