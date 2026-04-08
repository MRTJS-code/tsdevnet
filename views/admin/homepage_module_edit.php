<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($module['id']) ? 'Edit module' : 'New module' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-modules.php">Modules</a>
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

            <label>Module key
                <input type="text" name="module_key" value="<?= Util::e($module['module_key'] ?? '') ?>" required>
            </label>
            <label>Module type
                <select name="module_type" <?= !empty($module['id']) ? 'disabled' : '' ?>>
                    <?php foreach ($typeOptions as $typeOption): ?>
                        <option value="<?= Util::e($typeOption) ?>" <?= ($module['module_type'] ?? 'rich_text') === $typeOption ? 'selected' : '' ?>>
                            <?= Util::e(Util::homepageModuleLabel($typeOption)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($module['id'])): ?>
                    <input type="hidden" name="module_type" value="<?= Util::e($module['module_type']) ?>">
                <?php endif; ?>
            </label>
            <label>Eyebrow
                <input type="text" name="eyebrow" value="<?= Util::e($module['eyebrow'] ?? '') ?>">
            </label>
            <label>Title
                <input type="text" name="title" value="<?= Util::e($module['title'] ?? '') ?>" required>
            </label>
            <label>Intro text
                <textarea name="intro_text"><?= Util::e($module['intro_text'] ?? '') ?></textarea>
            </label>
            <label>Anchor id
                <input type="text" name="anchor_id" value="<?= Util::e($module['anchor_id'] ?? '') ?>">
            </label>
            <label>Style variant
                <input type="text" name="style_variant" value="<?= Util::e($module['style_variant'] ?? '') ?>">
            </label>
            <label>Group key
                <input type="text" name="group_key" value="<?= Util::e($module['group_key'] ?? '') ?>">
            </label>
            <label>Optional media document
                <select name="media_document_id">
                    <option value="0">None</option>
                    <?php foreach ($documents as $document): ?>
                        <option value="<?= (int) $document['id'] ?>" <?= (int) ($module['media_document_id'] ?? 0) === (int) $document['id'] ? 'selected' : '' ?>>
                            <?= Util::e($document['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Display order
                <input type="number" name="display_order" value="<?= (int) ($module['display_order'] ?? 0) ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($module['is_active']) ? 'checked' : '' ?>>
                <span>Module is active</span>
            </label>

            <?php if ($isInlineRichText): ?>
                <label>Body text
                    <textarea name="body_text"><?= Util::e($payload['body_text'] ?? '') ?></textarea>
                </label>
                <label>Primary CTA label
                    <input type="text" name="cta_label" value="<?= Util::e($payload['cta_label'] ?? '') ?>">
                </label>
                <label>Primary CTA URL
                    <input type="text" name="cta_url" value="<?= Util::e($payload['cta_url'] ?? '') ?>">
                </label>
                <label>Secondary CTA label
                    <input type="text" name="secondary_cta_label" value="<?= Util::e($payload['secondary_cta_label'] ?? '') ?>">
                </label>
                <label>Secondary CTA URL
                    <input type="text" name="secondary_cta_url" value="<?= Util::e($payload['secondary_cta_url'] ?? '') ?>">
                </label>
            <?php elseif (!empty($payloadEditorPath)): ?>
                <p class="help-text">This module uses typed payload records managed in a dedicated editor.</p>
                <p><a href="<?= Util::e($payloadEditorPath) ?>">Open payload editor</a></p>
            <?php endif; ?>

            <button class="btn primary" type="submit">Save module</button>
        </form>
    </section>
</main>
