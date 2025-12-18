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

$action = $_POST['action'] ?? null;
$csrfOk = Util::verifyCsrf($_POST['csrf_token'] ?? '');
if ($action && !$csrfOk) {
    http_response_code(400);
    echo 'Invalid CSRF';
    exit;
}

if ($action && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $statusMap = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'block' => 'blocked',
    ];
    if (isset($statusMap[$action])) {
        $stmt = $pdo->prepare('UPDATE users SET status = ?, approved_at = (CASE WHEN ? = "approved" THEN NOW() ELSE approved_at END), updated_at = NOW() WHERE id = ?');
        $stmt->execute([$statusMap[$action], $statusMap[$action], $userId]);
    }
    if ($action === 'note' && isset($_POST['admin_notes'])) {
        $stmt = $pdo->prepare('UPDATE users SET admin_notes = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([trim($_POST['admin_notes']), $userId]);
    }
}

$pending = $pdo->query('SELECT id, name, email, company, role_type, created_at FROM users WHERE status = "pending" ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Users</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
<header class="app-header">
    <h1>Admin console</h1>
    <div><a class="btn ghost" href="/logout.php">Exit portal</a></div>
</header>
<main class="admin">
    <section>
        <h2>Pending users</h2>
        <?php if (!$pending): ?>
            <p>No pending users.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr><th>Name</th><th>Email</th><th>Company</th><th>Role</th><th>Requested</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $row): ?>
                    <tr>
                        <td><?php echo Util::e($row['name']); ?></td>
                        <td><?php echo Util::e($row['email']); ?></td>
                        <td><?php echo Util::e($row['company']); ?></td>
                        <td><?php echo Util::e($row['role_type']); ?></td>
                        <td><?php echo Util::e($row['created_at']); ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?php echo Util::e(Util::csrfToken()); ?>">
                                <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                                <button class="btn small" name="action" value="approve">Approve</button>
                                <button class="btn small ghost" name="action" value="reject">Reject</button>
                                <button class="btn small danger" name="action" value="block">Block</button>
                                <a class="btn small ghost" href="/admin/user.php?id=<?php echo (int)$row['id']; ?>">View</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
