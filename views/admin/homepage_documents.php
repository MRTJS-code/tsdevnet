<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Documents</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage.php">Homepage hub</a>
        <a class="btn primary" href="/admin/homepage-document-edit.php">New document</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= Util::e($entry['document_key']) ?></td>
                        <td><?= Util::e($entry['document_type']) ?></td>
                        <td><?= Util::e($entry['title']) ?></td>
                        <td><?= (int) $entry['sort_order'] ?></td>
                        <td><?= !empty($entry['is_active']) ? 'Yes' : 'No' ?></td>
                        <td>
                            <div class="inline-form">
                                <a class="btn small ghost" href="/admin/homepage-document-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a>
                                <form method="post" action="/admin/homepage-document-edit.php?id=<?= (int) $entry['id'] ?>" onsubmit="return confirm('Are you sure you want to delete this document?');">
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
