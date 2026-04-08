<?php
use App\Support\Security;
use App\Support\Util;
?>
<main class="form-page admin-login">
    <div class="form-page__header">
        <p class="eyebrow">Admin Console</p>
        <h1>Sign in</h1>
        <p class="lede">Admin access is now database-backed. Seed the initial admin user locally, then manage content through the bespoke CMS pages.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <?php foreach ($errors as $error): ?>
                <p><?= Util::e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($adminCount)): ?>
        <div class="notice error">
            <p>No active admin users exist yet. Run the Phase 1B seed/bootstrap script to create the initial admin account before signing in.</p>
        </div>
    <?php endif; ?>

    <form method="post" class="card form">
        <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
        <label>Email
            <input type="email" name="email" required value="<?= Util::e($old['email'] ?? '') ?>">
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn primary">Sign in</button>
    </form>
</main>
