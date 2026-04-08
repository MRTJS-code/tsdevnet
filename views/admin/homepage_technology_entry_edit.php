<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit technology entry' : 'New technology entry' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-technologies.php">Back</a>
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
            <label>Group
                <select name="group_id" required>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= (int) $group['id'] ?>" <?= (int) $entry['group_id'] === (int) $group['id'] ? 'selected' : '' ?>><?= Util::e($group['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Technology label
                <input type="text" name="label" value="<?= Util::e($entry['label']) ?>" required>
            </label>
            <label>Detail text
                <textarea name="detail_text"><?= Util::e($entry['detail_text']) ?></textarea>
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <div class="actions">
                <button class="btn primary" type="submit">Save technology entry</button>
                <?php if (!empty($entry['id'])): ?>
                    <button class="btn danger" type="submit" name="form_action" value="delete" onclick="return confirm('Are you sure you want to delete this technology entry?');">Delete entry</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>
