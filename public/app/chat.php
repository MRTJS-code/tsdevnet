<?php
declare(strict_types=1);

use App\Http\Response;
use App\Support\Security;
use App\Support\Util;

$app = require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['error' => 'Method not allowed'], 405);
}

$config = $app['config'];
Security::enforceSameOrigin($config);

$user = $app['services']['auth']->currentUser();
if (!$user) {
    Response::json(['error' => 'Unauthorized'], 401);
}

if (!Security::verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    Response::json(['error' => 'Invalid CSRF token'], 403);
}

if (stripos((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') === false) {
    Response::json(['error' => 'Invalid content type'], 400);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$message = trim((string) ($payload['message'] ?? ''));
if ($message === '') {
    Response::json(['error' => 'Message required'], 400);
}

$tier = Util::tierFromStatus($user['status'], $config);
$rate = $app['services']['rate_limits']->attempt(
    'chat:' . date('Y-m-d') . ':user:' . $user['id'],
    (int) $tier['limit'],
    86400,
    Util::clientIp(),
    (int) $user['id']
);

if (!$rate['allowed']) {
    Response::json([
        'error' => 'Daily message limit reached',
        'remaining' => 0,
        'tier' => $tier['label'],
    ], 429);
}

if (empty($_SESSION['conversation_id'])) {
    $_SESSION['conversation_id'] = $app['services']['chat']->beginConversation((int) $user['id'], $tier['label'], Util::clientIp(), Util::userAgent());
}

$reply = $app['services']['chat']->reply(
    (int) $_SESSION['conversation_id'],
    (int) $user['id'],
    $message,
    ['status' => $user['status']],
    Util::clientIp()
);

Response::json([
    'reply' => $reply,
    'remaining' => $rate['remaining'],
    'tier' => $tier['label'],
]);
