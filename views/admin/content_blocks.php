<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Flexible CMS</p>
        <h1>Flexible content blocks</h1>
        <p class="help-text">These blocks now act as wrapper sections for the top, middle, and bottom homepage placements.</p>
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
                    <th>Placement</th>
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
                        <td><?= Util::e(ucfirst((string) ($block['homepage_position'] ?? 'top'))) ?></td>
                        <td><?= Util::e($block['title']) ?></td>
                        <td><?= (int) $block['sort_order'] ?></td>
                        <td><?= !empty($block['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a href="/admin/content-items.php?block_id=<?= (int) $block['id'] ?>">View items</a></td>
                        <td>
                            <div class="inline-form">
                                <a class="btn small ghost" href="/admin/content-block-edit.php?id=<?= (int) $block['id'] ?>">Edit</a>
                                <form method="post" action="/admin/content-block-edit.php?id=<?= (int) $block['id'] ?>" onsubmit="return confirm('Are you sure you want to delete this block and its items?');">
                                    <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                                    <input type="hidden" name="form_action" value="delete">
                                    <button class="btn small danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
