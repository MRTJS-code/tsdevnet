<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Admin Console</p>
        <h1><?= Util::e($detail['user']['name']) ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/index.php">Back</a>
    </div>
</header>

<main class="admin-shell">
    <section class="card">
        <div class="detail-grid">
            <div>
                <p><strong>Email:</strong> <?= Util::e($detail['user']['email']) ?></p>
                <p><strong>Company:</strong> <?= Util::e($detail['user']['company']) ?></p>
                <p><strong>Role type:</strong> <?= Util::e($detail['user']['role_type']) ?></p>
                <p><strong>Status:</strong> <?= Util::e($detail['user']['status']) ?></p>
                <p><strong>LinkedIn:</strong> <?= Util::e($detail['user']['linkedin_url']) ?></p>
                <p><strong>Hiring for:</strong> <?= nl2br(Util::e($detail['user']['hiring_for'])) ?></p>
                <p><strong>Created:</strong> <?= Util::e($detail['user']['created_at']) ?></p>
                <p><strong>Last login:</strong> <?= Util::e($detail['user']['last_login_at']) ?></p>
            </div>
            <form method="post" class="form">
                <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
                <label>Internal notes
                    <textarea name="admin_notes"><?= Util::e($detail['user']['admin_notes'] ?? '') ?></textarea>
                </label>
                <button class="btn primary" type="submit">Save notes</button>
            </form>
        </div>
    </section>

    <section class="card">
        <h2>Conversations</h2>
        <?php if (empty($detail['conversations'])): ?>
            <p>No conversations yet.</p>
        <?php else: ?>
            <ul class="feature-list">
                <?php foreach ($detail['conversations'] as $conversation): ?>
                    <li>Conversation #<?= (int) $conversation['id'] ?> | <?= Util::e($conversation['tier_at_time']) ?> | <?= Util::e($conversation['started_at']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Messages</h2>
        <?php if (empty($detail['messages'])): ?>
            <p>No messages yet.</p>
        <?php else: ?>
            <div class="messages-log">
                <?php foreach ($detail['messages'] as $message): ?>
                    <div class="log-row <?= $message['sender'] === 'assistant' ? 'assistant' : 'user' ?>">
                        <strong><?= Util::e($message['sender']) ?>:</strong>
                        <span><?= nl2br(Util::e($message['content'])) ?></span>
                        <small><?= Util::e($message['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
