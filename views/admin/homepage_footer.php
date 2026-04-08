<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Footer and contact settings</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage.php">Homepage hub</a>
        <a class="btn ghost" href="/admin/homepage-documents.php">Documents</a>
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
            <label>Footer heading
                <input type="text" name="footer_heading" value="<?= Util::e($settings['footer_heading'] ?? '') ?>">
            </label>
            <label>Footer body
                <textarea name="footer_body"><?= Util::e($settings['footer_body'] ?? '') ?></textarea>
            </label>
            <label>Contact email
                <input type="email" name="contact_email" value="<?= Util::e($settings['contact_email'] ?? '') ?>">
            </label>
            <label>Contact phone
                <input type="text" name="contact_phone" value="<?= Util::e($settings['contact_phone'] ?? '') ?>">
            </label>
            <label>Contact location
                <input type="text" name="contact_location" value="<?= Util::e($settings['contact_location'] ?? '') ?>">
            </label>
            <label>LinkedIn URL
                <input type="text" name="linkedin_url" value="<?= Util::e($settings['linkedin_url'] ?? '') ?>">
            </label>
            <label>GitHub URL
                <input type="text" name="github_url" value="<?= Util::e($settings['github_url'] ?? '') ?>">
            </label>
            <button class="btn primary" type="submit">Save footer settings</button>
        </form>
    </section>
</main>
