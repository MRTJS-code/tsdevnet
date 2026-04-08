<?php
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Assistant</p>
        <h1>Knowledge rules</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/index.php">Dashboard</a>
        <a class="btn primary" href="/admin/assistant-knowledge-edit.php">New rule</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Trigger</th>
                    <th>Tier</th>
                    <th>Priority</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= Util::e($entry['knowledge_key']) ?></td>
                        <td><?= Util::e($entry['trigger_type']) ?>: <?= Util::e($entry['trigger_value']) ?></td>
                        <td><?= Util::e($entry['minimum_access_tier']) ?></td>
                        <td><?= (int) $entry['priority'] ?></td>
                        <td><?= !empty($entry['is_active']) ? 'Yes' : 'No' ?></td>
                        <td><a class="btn small ghost" href="/admin/assistant-knowledge-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

