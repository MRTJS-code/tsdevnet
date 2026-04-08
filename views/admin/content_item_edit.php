<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Flexible CMS</p>
        <h1><?= !empty($item['id']) ? 'Edit flexible item' : 'New flexible item' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/content-items.php<?= !empty($item['block_id']) ? '?block_id=' . (int) $item['block_id'] : '' ?>">Back</a>
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
        <?php if (empty($blocks)): ?>
            <p>Create a homepage block first, then add repeatable items to it.</p>
        <?php else: ?>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
            <label>Block
                <select name="block_id" required>
                    <?php foreach ($blocks as $blockOption): ?>
                        <option value="<?= (int) $blockOption['id'] ?>" <?= (int) $item['block_id'] === (int) $blockOption['id'] ? 'selected' : '' ?>><?= Util::e(Util::sectionLabel($blockOption['section_key'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Item key
                <input type="text" name="item_key" value="<?= Util::e($item['item_key']) ?>">
            </label>
            <label>Label
                <input type="text" name="label" value="<?= Util::e($item['label']) ?>">
            </label>
            <label>Title
                <input type="text" name="title" value="<?= Util::e($item['title']) ?>">
            </label>
            <label>Body text
                <textarea name="body_text"><?= Util::e($item['body_text']) ?></textarea>
            </label>
            <label>Link URL
                <input type="text" name="link_url" value="<?= Util::e($item['link_url']) ?>">
            </label>
            <label>Metadata JSON
                <textarea name="meta_json"><?= Util::e($item['meta_json']) ?></textarea>
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $item['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($item['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <div class="actions">
                <button class="btn primary" type="submit">Save item</button>
                <?php if (!empty($item['id'])): ?>
                    <button class="btn danger" type="submit" name="form_action" value="delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete item</button>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </section>
</main>
