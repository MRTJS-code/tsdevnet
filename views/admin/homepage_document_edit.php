<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit document or link' : 'New document or link' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-documents.php">Back</a>
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
        <form method="post" class="form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
            <label>Document key
                <select name="document_key">
                    <?php foreach ($allowedKeys as $key): ?>
                        <option value="<?= Util::e($key) ?>" <?= $entry['document_key'] === $key ? 'selected' : '' ?>><?= Util::e($key) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Document type
                <select name="document_type">
                    <?php foreach ($allowedTypes as $type): ?>
                        <option value="<?= Util::e($type) ?>" <?= $entry['document_type'] === $type ? 'selected' : '' ?>><?= Util::e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title
                <input type="text" name="title" value="<?= Util::e($entry['title']) ?>" required>
            </label>
            <label>Description
                <textarea name="description_text"><?= Util::e($entry['description_text']) ?></textarea>
            </label>
            <label>External URL
                <input type="text" name="external_url" value="<?= Util::e($entry['external_url']) ?>">
            </label>
            <label>Upload file
                <input type="file" name="upload_file" accept=".jpg,.jpeg,.png,.webp,.pdf">
            </label>
            <?php if (!empty($entry['file_path'])): ?>
                <p class="help-text">Current file: <a href="<?= Util::e($entry['file_path']) ?>" target="_blank" rel="noreferrer"><?= Util::e($entry['file_path']) ?></a></p>
            <?php endif; ?>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save document</button>
        </form>
    </section>
</main>
