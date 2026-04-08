<?php
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Flexible CMS</p>
        <h1>Flexible content items</h1>
        <?php if (!empty($selectedBlock)): ?>
            <p class="help-text">Filtered to <?= Util::e(Util::sectionLabel($selectedBlock['section_key'])) ?></p>
        <?php endif; ?>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/index.php">Dashboard</a>
        <a class="btn primary" href="/admin/content-item-edit.php<?= !empty($selectedBlock) ? '?block_id=' . (int) $selectedBlock['id'] : '' ?>">New item</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Block</th>
                    <th>Label</th>
                    <th>Title</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= Util::e(Util::sectionLabel($item['section_key'] ?? ($selectedBlock['section_key'] ?? ''))) ?></td>
                        <td><?= Util::e($item['label']) ?></td>
                        <td><?= Util::e($item['title']) ?></td>
                        <td><?= (int) $item['sort_order'] ?></td>
                        <td><?= !empty($item['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a class="btn small ghost" href="/admin/content-item-edit.php?id=<?= (int) $item['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
