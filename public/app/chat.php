<?php
require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin && parse_url($origin, PHP_URL_HOST) !== $host) {
    http_response_code(403);
    echo json_encode(['error' => 'Bad origin']);
    exit;
}

$pdo = Db::get($config);
$auth = new Auth($pdo, $config);
$user = $auth->currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!Util::verifyCsrf($csrfHeader)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF']);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid content type']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message required']);
    exit;
}

$tier = Util::tierFromStatus($user['status']);
$limit = $tier['limit'];

$rl = new RateLimiter($pdo, (int)$user['id'], Util::clientIp());
$rate = $rl->attempt('chat:' . date('Y-m-d') . ':user:' . $user['id'], $limit, 86400);
if (!$rate['allowed']) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit reached', 'remaining' => 0, 'tier' => $tier['label']]);
    exit;
}

$chat = new ChatService($pdo);
if (empty($_SESSION['conversation_id'])) {
    $_SESSION['conversation_id'] = $chat->startConversation((int)$user['id'], $tier['label'], Util::clientIp(), Util::userAgent());
}
$convId = (int)$_SESSION['conversation_id'];

$chat->addMessage($convId, 'user', $message);
$reply = $chat->generateReply($message);
$chat->addMessage($convId, 'assistant', $reply);

header('Content-Type: application/json');
echo json_encode([
    'reply' => $reply,
    'remaining' => $rate['remaining'],
    'tier' => $tier['label'],
]);
