<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit certification' : 'New certification' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-certifications.php">Back</a>
    </div>
</header>

<main class="admin-shell">
    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <?php foreach ($errors as $error): ?>
                <p><?= Util::e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
            <label>Certification name
                <input type="text" name="certification_name" value="<?= Util::e($entry['certification_name']) ?>" required>
            </label>
            <label>Issuer
                <input type="text" name="issuer" value="<?= Util::e($entry['issuer']) ?>">
            </label>
            <label>Issued label
                <input type="text" name="issued_label" value="<?= Util::e($entry['issued_label']) ?>">
            </label>
            <label>Credential URL
                <input type="text" name="credential_url" value="<?= Util::e($entry['credential_url']) ?>">
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save certification</button>
        </form>
    </section>
</main>
