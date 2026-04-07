<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Admin Console</p>
        <h1>Pending recruiter requests</h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/logout.php">Sign out</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <p class="help-text">Manual approval controls who gets full assistant access. All state changes are CSRF-protected and logged to the audit table.</p>
    </section>

    <section class="card">
        <?php if (empty($pendingUsers)): ?>
            <p>No pending users.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Role</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendingUsers as $row): ?>
                        <tr>
                            <td><?= Util::e($row['name']) ?></td>
                            <td><?= Util::e($row['email']) ?></td>
                            <td><?= Util::e($row['company']) ?></td>
                            <td><?= Util::e($row['role_type']) ?></td>
                            <td><?= Util::e($row['created_at']) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                                    <input type="hidden" name="user_id" value="<?= (int) $row['id'] ?>">
                                    <button class="btn small" name="action" value="approve">Approve</button>
                                    <button class="btn small ghost" name="action" value="reject">Reject</button>
                                    <button class="btn small danger" name="action" value="block">Block</button>
                                    <a class="btn small ghost" href="/admin/user.php?id=<?= (int) $row['id'] ?>">Review</a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

