<?php
use App\Support\Security;
use App\Support\Util;
?>
<main class="form-page admin-login">
    <div class="form-page__header">
        <p class="eyebrow">Admin Console</p>
        <h1>Sign in</h1>
        <p class="lede">Session-based admin access replaces HTTP Basic Auth. Keep the configured password hash out of version control.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <?php foreach ($errors as $error): ?>
                <p><?= Util::e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card form">
        <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
        <label>Username
            <input type="text" name="username" required value="<?= Util::e($old['username'] ?? '') ?>">
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn primary">Sign in</button>
    </form>
</main>

