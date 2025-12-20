<?php
require __DIR__ . '/../src/bootstrap.php';

$pdo = Db::get($config);
$auth = new Auth($pdo, $config);

$token = $_GET['token'] ?? '';
if (!$token) {
    http_response_code(400);
    echo 'Missing token.';
    exit;
}

$userId = $auth->verifyAndLogin($token);
if (!$userId) {
    http_response_code(400);
    echo 'Invalid or expired token.';
    exit;
}

header('Location: /app/index.php');
exit;
