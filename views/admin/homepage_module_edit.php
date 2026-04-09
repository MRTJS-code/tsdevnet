<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($module['id']) ? 'Edit block module' : 'New block module' ?></h1>
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

            <?php if (in_array($module['module_type'], ['rich_text', 'cta_banner', 'media_text'], true)): ?>
                <label>Body text
                    <textarea name="body_text"><?= Util::e($payloadState['body_text'] ?? '') ?></textarea>
                </label>
                <label>Primary CTA label
                    <input type="text" name="primary_cta_label" value="<?= Util::e($payloadState['primary_cta_label'] ?? '') ?>">
                </label>
                <label>Primary CTA URL
                    <input type="text" name="primary_cta_url" value="<?= Util::e($payloadState['primary_cta_url'] ?? '') ?>">
                </label>
                <label>Secondary CTA label
                    <input type="text" name="secondary_cta_label" value="<?= Util::e($payloadState['secondary_cta_label'] ?? '') ?>">
                </label>
                <label>Secondary CTA URL
                    <input type="text" name="secondary_cta_url" value="<?= Util::e($payloadState['secondary_cta_url'] ?? '') ?>">
                </label>
                <?php if (($module['module_type'] ?? '') === 'media_text'): ?>
                    <label>Media position
                        <select name="media_position">
                            <option value="right" <?= ($payloadState['media_position'] ?? 'right') === 'right' ? 'selected' : '' ?>>Media right</option>
                            <option value="left" <?= ($payloadState['media_position'] ?? '') === 'left' ? 'selected' : '' ?>>Media left</option>
                        </select>
                    </label>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array($module['module_type'], ['timeline', 'pill_cards', 'case_studies', 'list', 'quote_cards'], true)): ?>
                <div class="card">
                    <p class="eyebrow">Module items</p>
                    <?php if (empty($module['id'])): ?>
                        <p class="help-text">Save the module first, then manage its items here.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <?php foreach ($itemFields as $field): ?>
                                        <th><?= Util::e(ucwords(str_replace('_', ' ', $field))) ?></th>
                                    <?php endforeach; ?>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($payloadState['items'])): ?>
                                    <tr>
                                        <td colspan="<?= count($itemFields) + 2 ?>">No items yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($payloadState['items'] as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <?php foreach ($itemFields as $field): ?>
                                                <?php $cellText = (string) ($item[$field] ?? ''); ?>
                                                <td><?= Util::e(strlen($cellText) > 80 ? substr($cellText, 0, 77) . '...' : $cellText) ?></td>
                                            <?php endforeach; ?>
                                            <td>
                                                <div class="inline-form">
                                                    <form method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                                                        <input type="hidden" name="form_action" value="edit_item">
                                                        <input type="hidden" name="item_index" value="<?= $index ?>">
                                                        <button class="btn small ghost" type="submit">Edit</button>
                                                    </form>
                                                    <form method="post" onsubmit="return confirm('Delete this item?');">
                                                        <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                                                        <input type="hidden" name="form_action" value="delete_item">
                                                        <input type="hidden" name="item_index" value="<?= $index ?>">
                                                        <button class="btn small danger" type="submit">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card">
                            <p class="eyebrow"><?= $editingItemIndex >= 0 ? 'Edit item' : 'Add new item' ?></p>
                            <form method="post" class="form">
                                <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                                <input type="hidden" name="form_action" value="save_item">
                                <input type="hidden" name="editing_item_index" value="<?= (int) $editingItemIndex ?>">
                                <?php foreach ($itemFields as $field): ?>
                                    <label><?= Util::e(ucwords(str_replace('_', ' ', $field))) ?>
                                        <?php if (str_contains($field, 'text') || str_contains($field, 'body') || str_contains($field, 'summary') || str_contains($field, 'detail') || $field === 'quote_text'): ?>
                                            <textarea name="item[<?= Util::e($field) ?>]"><?= Util::e($itemEditor[$field] ?? '') ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="item[<?= Util::e($field) ?>]" value="<?= Util::e($itemEditor[$field] ?? '') ?>">
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                                <div class="actions">
                                    <button class="btn primary" type="submit"><?= $editingItemIndex >= 0 ? 'Update item' : 'Add item' ?></button>
                                    <?php if ($editingItemIndex >= 0): ?>
                                        <a class="btn ghost" href="/admin/homepage-module-edit.php?id=<?= (int) $module['id'] ?>">Cancel edit</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <button class="btn primary" type="submit">Save module</button>
        </form>
    </section>
</main>
