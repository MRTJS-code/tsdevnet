<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Flexible CMS</p>
        <h1><?= !empty($block['id']) ? 'Edit flexible block' : 'New flexible block' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/content-blocks.php">Back</a>
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
            <label>Section key
                <select name="section_key" required>
                    <?php foreach ($sectionOptions as $key): ?>
                        <option value="<?= Util::e($key) ?>" <?= $block['section_key'] === $key ? 'selected' : '' ?>><?= Util::e(Util::sectionLabel($key)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Homepage placement
                <select name="homepage_position" required>
                    <?php foreach ($positionOptions as $position): ?>
                        <option value="<?= Util::e($position) ?>" <?= ($block['homepage_position'] ?? 'top') === $position ? 'selected' : '' ?>><?= Util::e(ucfirst($position)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title
                <input type="text" name="title" value="<?= Util::e($block['title']) ?>">
            </label>
            <label>Subtitle / eyebrow
                <input type="text" name="subtitle" value="<?= Util::e($block['subtitle']) ?>">
            </label>
            <label>Body text
                <textarea name="body_text"><?= Util::e($block['body_text']) ?></textarea>
            </label>
            <p class="help-text">Use these wrapper blocks to introduce the typed Core Strengths, Grouped Capability, or chatbot/footer-adjacent sections.</p>
            <label>Metadata JSON
                <textarea name="meta_json"><?= Util::e($block['meta_json']) ?></textarea>
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $block['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($block['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <div class="actions">
                <button class="btn primary" type="submit">Save block</button>
                <?php if (!empty($block['id'])): ?>
                    <button class="btn danger" type="submit" name="form_action" value="delete" onclick="return confirm('Are you sure you want to delete this block and its items?');">Delete block</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>
