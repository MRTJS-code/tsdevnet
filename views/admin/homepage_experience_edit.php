<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit timeline entry' : 'New timeline entry' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-experience.php">Back</a>
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
            <label>Role title
                <input type="text" name="role_title" value="<?= Util::e($entry['role_title']) ?>" required>
            </label>
            <label>Organisation
                <input type="text" name="organisation" value="<?= Util::e($entry['organisation']) ?>" required>
            </label>
            <label>Period label
                <input type="text" name="period_label" value="<?= Util::e($entry['period_label']) ?>" required>
            </label>
            <label>Summary
                <textarea name="summary"><?= Util::e($entry['summary']) ?></textarea>
            </label>
            <label>Highlights
                <textarea name="highlight_lines" rows="5" placeholder="Enter one highlight per line"><?= Util::e($entry['highlight_lines'] ?? '') ?></textarea>
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save entry</button>
        </form>
    </section>
</main>
