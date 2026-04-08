<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit portfolio item' : 'New portfolio item' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-portfolio.php">Back</a>
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
            <label>Title
                <input type="text" name="title" value="<?= Util::e($entry['title']) ?>" required>
            </label>
            <label>Summary
                <textarea name="summary"><?= Util::e($entry['summary']) ?></textarea>
            </label>
            <label>Outcome
                <textarea name="outcome"><?= Util::e($entry['outcome']) ?></textarea>
            </label>
            <label>Link URL
                <input type="text" name="link_url" value="<?= Util::e($entry['link_url']) ?>">
            </label>
            <label>Link label
                <input type="text" name="link_label" value="<?= Util::e($entry['link_label']) ?>">
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <div class="actions">
                <button class="btn primary" type="submit">Save portfolio item</button>
                <?php if (!empty($entry['id'])): ?>
                    <button class="btn danger" type="submit" name="form_action" value="delete" onclick="return confirm('Are you sure you want to delete this portfolio item?');">Delete portfolio item</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>
