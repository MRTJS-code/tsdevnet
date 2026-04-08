<?php
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Certifications</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage.php">Homepage hub</a>
        <a class="btn primary" href="/admin/homepage-certification-edit.php">New certification</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Certification</th>
                    <th>Issuer</th>
                    <th>Issued</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= Util::e($entry['certification_name']) ?></td>
                        <td><?= Util::e($entry['issuer']) ?></td>
                        <td><?= Util::e($entry['issued_label']) ?></td>
                        <td><?= (int) $entry['sort_order'] ?></td>
                        <td><?= !empty($entry['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a class="btn small ghost" href="/admin/homepage-certification-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
