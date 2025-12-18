<?php
require __DIR__ . '/../../src/bootstrap.php';
$pdo = Db::get($config);
$auth = new Auth($pdo, $config);
$auth->requireLogin();
$user = $auth->currentUser();
$tier = Util::tierFromStatus($user['status']);
$csrf = Util::csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portal | Chat</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script defer src="/assets/js/app.js"></script>
    <meta name="csrf-token" content="<?php echo Util::e($csrf); ?>">
</head>
<body class="page">
<header class="app-header">
    <div>
        <h1>Recruiter Portal</h1>
        <p>Status: <?php echo Util::e($user['status']); ?> · Tier: <?php echo Util::e($tier['label']); ?> (<?php echo $tier['limit']; ?> msgs/day)</p>
    </div>
    <div>
        <a class="btn ghost" href="/logout.php">Logout</a>
    </div>
</header>
<main class="chat">
    <div class="chat__window" id="chat-window">
        <div class="message system">Welcome! Pending users have limited demo responses. Share role context to start.</div>
    </div>
    <form id="chat-form" class="chat__form">
        <input type="text" id="chat-input" name="message" placeholder="Ask about role, skills, or process..." autocomplete="off" required>
        <button type="submit" class="btn primary">Send</button>
    </form>
    <p class="help-text">Need full access? Approval unlocks richer responses and higher limits.</p>
</main>
</body>
</html>
