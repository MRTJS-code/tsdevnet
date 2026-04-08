<?php
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Technology groups and entries</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage.php">Homepage hub</a>
        <a class="btn ghost" href="/admin/homepage-technology-group-edit.php">New group</a>
        <a class="btn primary" href="/admin/homepage-technology-entry-edit.php">New entry</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <h2>Groups</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Title</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= Util::e($group['group_key']) ?></td>
                        <td><?= Util::e($group['title']) ?></td>
                        <td><?= (int) $group['sort_order'] ?></td>
                        <td><?= !empty($group['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a class="btn small ghost" href="/admin/homepage-technology-group-edit.php?id=<?= (int) $group['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2>Entries</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Group</th>
                    <th>Label</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= Util::e($entry['group_title']) ?></td>
                        <td><?= Util::e($entry['label']) ?></td>
                        <td><?= (int) $entry['sort_order'] ?></td>
                        <td><?= !empty($entry['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a class="btn small ghost" href="/admin/homepage-technology-entry-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
