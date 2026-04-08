<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($group['id']) ? 'Edit technology group' : 'New technology group' ?></h1>
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
            <label>Group key
                <select name="group_key">
                    <?php foreach ($allowedKeys as $key): ?>
                        <option value="<?= Util::e($key) ?>" <?= $group['group_key'] === $key ? 'selected' : '' ?>><?= Util::e($key) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title
                <input type="text" name="title" value="<?= Util::e($group['title']) ?>" required>
            </label>
            <label>Intro text
                <textarea name="intro_text"><?= Util::e($group['intro_text']) ?></textarea>
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $group['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($group['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save group</button>
        </form>
    </section>
</main>
