<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1>Ordered middle-page modules</h1>
        <p class="help-text">Hero and footer remain fixed. Everything between them is managed here.</p>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage.php">Homepage hub</a>
        <a class="btn primary" href="/admin/homepage-module-edit.php">New module</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Order</th>
                    <th>Key</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($modules as $entry): ?>
                    <tr>
                        <td><?= (int) $entry['display_order'] ?></td>
                        <td><?= Util::e($entry['module_key']) ?></td>
                        <td><?= Util::e(Util::homepageModuleLabel((string) $entry['module_type'])) ?></td>
                        <td><?= Util::e($entry['title']) ?></td>
                        <td><?= !empty($entry['is_active']) ? 'Yes' : 'No' ?></td>
                        <td>
                            <div class="inline-form">
                                <a class="btn small ghost" href="/admin/homepage-module-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a>
                                <form method="post" action="/admin/homepage-module-edit.php?id=<?= (int) $entry['id'] ?>" onsubmit="return confirm('Delete this homepage module?');">
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
