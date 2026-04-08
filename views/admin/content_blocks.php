<?php
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">CMS</p>
        <h1>Homepage blocks</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/index.php">Dashboard</a>
        <a class="btn primary" href="/admin/content-block-edit.php">New block</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($blocks as $block): ?>
                    <tr>
                        <td><?= Util::e(Util::sectionLabel($block['section_key'])) ?></td>
                        <td><?= Util::e($block['title']) ?></td>
                        <td><?= (int) $block['sort_order'] ?></td>
                        <td><?= !empty($block['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a href="/admin/content-items.php?block_id=<?= (int) $block['id'] ?>">View items</a></td>
                        <td><a class="btn small ghost" href="/admin/content-block-edit.php?id=<?= (int) $block['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

