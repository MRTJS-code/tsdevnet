<?php
use App\Support\Security;
use App\Support\Util;
?>
<main class="form-page">
    <div class="form-page__header">
        <p class="eyebrow">Recruiter Access</p>
        <h1>Request portal access</h1>
        <p class="lede">The assistant is not public. Access requests are reviewed, and new users start on a pending demo tier.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="notice success">
            <p>Check your email for a secure login link. If approved later, the same login flow unlocks the fuller recruiter tier.</p>
            <?php if (!empty($magicLink)): ?>
                <p><strong>Dev link:</strong> <a href="<?= Util::e($magicLink) ?>"><?= Util::e($magicLink) ?></a></p>
            <?php endif; ?>
            <p><a class="btn ghost" href="/login.php">Back to login</a></p>
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
            <label>Name*
                <input type="text" name="name" required value="<?= Util::e($old['name'] ?? '') ?>">
            </label>
            <label>Email*
                <input type="email" name="email" required value="<?= Util::e($old['email'] ?? '') ?>">
            </label>
            <label>Company*
                <input type="text" name="company" required value="<?= Util::e($old['company'] ?? '') ?>">
            </label>
            <label>Role type*
                <select name="role_type" required>
                    <option value="">Select one</option>
                    <?php foreach (['Recruiter', 'Hiring Manager', 'Talent Partner', 'Other'] as $role): ?>
                        <option value="<?= Util::e($role) ?>" <?= ($old['role_type'] ?? '') === $role ? 'selected' : '' ?>><?= Util::e($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>LinkedIn URL
                <input type="url" name="linkedin_url" value="<?= Util::e($old['linkedin_url'] ?? '') ?>">
            </label>
            <label>What are you hiring for?
                <textarea name="hiring_for"><?= Util::e($old['hiring_for'] ?? '') ?></textarea>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="consent" value="1" <?= !empty($old['consent']) ? 'checked' : '' ?> required>
                <span>I consent to be contacted about this access request.</span>
            </label>
            <div class="cf-turnstile" data-sitekey="<?= Util::e($config['turnstile']['site_key'] ?? '') ?>"></div>
            <button type="submit" class="btn primary">Request access</button>
            <p class="help-text">Signup is protected by Turnstile and the login link is one-time use.</p>
        </form>
    <?php endif; ?>
</main>

