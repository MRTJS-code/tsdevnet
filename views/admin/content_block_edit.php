<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">CMS</p>
        <h1><?= !empty($block['id']) ? 'Edit block' : 'New block' ?></h1>
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
            <label>Title
                <input type="text" name="title" value="<?= Util::e($block['title']) ?>">
            </label>
            <label>Subtitle / eyebrow
                <input type="text" name="subtitle" value="<?= Util::e($block['subtitle']) ?>">
            </label>
            <label>Body text
                <textarea name="body_text"><?= Util::e($block['body_text']) ?></textarea>
            </label>
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
            <button class="btn primary" type="submit">Save block</button>
        </form>
    </section>
</main>

