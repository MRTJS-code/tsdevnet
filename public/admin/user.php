<?php
require __DIR__ . '/../../src/bootstrap.php';

$pdo = Db::get($config);

$user = $config['admin']['user'] ?? '';
$pass = $config['admin']['pass'] ?? '';
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $user || $_SERVER['PHP_AUTH_PW'] !== $pass) {
    header('WWW-Authenticate: Basic realm="Admin"');
    http_response_code(401);
    echo 'Auth required';
    exit;
}

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    echo 'User not found';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$record = $stmt->fetch();
if (!$record) {
    echo 'User not found';
    exit;
}

$convStmt = $pdo->prepare('SELECT * FROM conversations WHERE user_id = ? ORDER BY started_at DESC');
$convStmt->execute([$userId]);
$conversations = $convStmt->fetchAll();

$msgStmt = $pdo->prepare(
    'SELECT m.*, c.started_at FROM messages m JOIN conversations c ON c.id = m.conversation_id WHERE c.user_id = ? ORDER BY m.created_at DESC LIMIT 100'
);
$msgStmt->execute([$userId]);
$messages = $msgStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Util::verifyCsrf($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['admin_notes'])) {
        $stmt = $pdo->prepare('UPDATE users SET admin_notes = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([trim($_POST['admin_notes']), $userId]);
        $record['admin_notes'] = trim($_POST['admin_notes']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User detail</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
<header class="app-header">
    <h1>User: <?php echo Util::e($record['name']); ?></h1>
    <div><a class="btn ghost" href="/admin/index.php">Back</a></div>
</header>
<main class="admin">
    <section class="card">
        <p><strong>Email:</strong> <?php echo Util::e($record['email']); ?></p>
        <p><strong>Company:</strong> <?php echo Util::e($record['company']); ?></p>
        <p><strong>Role:</strong> <?php echo Util::e($record['role_type']); ?></p>
        <p><strong>Status:</strong> <?php echo Util::e($record['status']); ?></p>
        <p><strong>LinkedIn:</strong> <?php echo Util::e($record['linkedin_url']); ?></p>
        <p><strong>Hiring for:</strong> <?php echo nl2br(Util::e($record['hiring_for'])); ?></p>
        <p><strong>Created:</strong> <?php echo Util::e($record['created_at']); ?></p>
        <p><strong>Last login:</strong> <?php echo Util::e($record['last_login_at']); ?></p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Util::e(Util::csrfToken()); ?>">
            <label>Admin notes
                <textarea name="admin_notes"><?php echo Util::e($record['admin_notes'] ?? ''); ?></textarea>
            </label>
            <button class="btn primary" type="submit">Save notes</button>
        </form>
    </section>

    <section>
        <h2>Conversations</h2>
        <?php if (!$conversations): ?>
            <p>No conversations yet.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($conversations as $c): ?>
                    <li>ID <?php echo (int)$c['id']; ?> · Tier: <?php echo Util::e($c['tier_at_time']); ?> · Started: <?php echo Util::e($c['started_at']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section>
        <h2>Messages (latest 100)</h2>
        <?php if (!$messages): ?>
            <p>No messages.</p>
        <?php else: ?>
            <div class="messages-log">
                <?php foreach ($messages as $m): ?>
                    <div class="log-row <?php echo $m['sender'] === 'assistant' ? 'assistant' : 'user'; ?>">
                        <strong><?php echo Util::e($m['sender']); ?>:</strong>
                        <span><?php echo nl2br(Util::e($m['content'])); ?></span>
                        <small><?php echo Util::e($m['created_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
