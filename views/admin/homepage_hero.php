<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Hero settings</h1>
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
            <label>Site title
                <input type="text" name="site_title" value="<?= Util::e($settings['site_title'] ?? '') ?>">
            </label>
            <label>Hero eyebrow
                <input type="text" name="hero_eyebrow" value="<?= Util::e($settings['hero_eyebrow'] ?? '') ?>">
            </label>
            <label>Hero title
                <input type="text" name="hero_title" value="<?= Util::e($settings['hero_title'] ?? '') ?>" required>
            </label>
            <label>Hero summary
                <textarea name="hero_summary"><?= Util::e($settings['hero_summary'] ?? '') ?></textarea>
            </label>
            <label>Hero supporting text
                <textarea name="hero_supporting_text"><?= Util::e($settings['hero_supporting_text'] ?? '') ?></textarea>
            </label>
            <label>Profile name
                <input type="text" name="profile_name" value="<?= Util::e($settings['profile_name'] ?? '') ?>" required>
            </label>
            <label>Profile role
                <input type="text" name="profile_role" value="<?= Util::e($settings['profile_role'] ?? '') ?>">
            </label>
            <label>Profile location
                <input type="text" name="profile_location" value="<?= Util::e($settings['profile_location'] ?? '') ?>">
            </label>
            <label>Profile availability note
                <input type="text" name="profile_availability" value="<?= Util::e($settings['profile_availability'] ?? '') ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="open_to_work" value="1" <?= !empty($settings['open_to_work']) && $settings['open_to_work'] !== '0' ? 'checked' : '' ?>>
                <span>Open to work</span>
            </label>
            <label>CTA mode
                <select name="cta_mode">
                    <?php $ctaMode = $settings['cta_mode'] ?? 'register_request_chat'; ?>
                    <option value="register_request_chat" <?= $ctaMode === 'register_request_chat' ? 'selected' : '' ?>>Register and request to chat</option>
                    <option value="request_chat_access" <?= $ctaMode === 'request_chat_access' ? 'selected' : '' ?>>Request chat access</option>
                    <option value="contact_only" <?= $ctaMode === 'contact_only' ? 'selected' : '' ?>>Contact only</option>
                </select>
            </label>
            <label>Primary CTA label
                <input type="text" name="cta_primary_label" value="<?= Util::e($settings['cta_primary_label'] ?? '') ?>" required>
            </label>
            <label>Primary CTA URL
                <input type="text" name="cta_primary_url" value="<?= Util::e($settings['cta_primary_url'] ?? '') ?>" required>
            </label>
            <label>Secondary CTA label
                <input type="text" name="cta_secondary_label" value="<?= Util::e($settings['cta_secondary_label'] ?? '') ?>">
            </label>
            <label>Secondary CTA URL
                <input type="text" name="cta_secondary_url" value="<?= Util::e($settings['cta_secondary_url'] ?? '') ?>">
            </label>
            <button class="btn primary" type="submit">Save hero settings</button>
        </form>
    </section>
</main>
