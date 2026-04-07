<?php
declare(strict_types=1);

use App\Chat\RuleBasedChatProvider;
use App\Repositories\AuditLogRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;
use App\Repositories\RateLimitRepository;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use App\Services\AdminAuthService;
use App\Services\ApprovalService;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\ChatService;
use App\Services\MagicLinkService;
use App\Services\RateLimitService;
use App\Services\UserService;
use App\Support\Database;
use App\Support\Mailer;
use App\Support\Security;
use App\Support\Session;

$root = dirname(__DIR__);

require_once __DIR__ . '/Support/Autoloader.php';

$config = require $root . '/config/app.php';

date_default_timezone_set('UTC');
Session::start($config);
Security::sendHeaders($config);

if (is_file($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}

$pdo = Database::connect($config);

$userRepository = new UserRepository($pdo);
$tokenRepository = new TokenRepository($pdo);
$conversationRepository = new ConversationRepository($pdo);
$messageRepository = new MessageRepository($pdo);
$auditLogRepository = new AuditLogRepository($pdo);
$rateLimitRepository = new RateLimitRepository($pdo);

$auditService = new AuditService($auditLogRepository);
$rateLimitService = new RateLimitService($rateLimitRepository);
$magicLinkService = new MagicLinkService($pdo, $tokenRepository, $userRepository, $auditService, $config);
$mailer = new Mailer($config);
$authService = new AuthService($userRepository, $auditService);
$userService = new UserService($userRepository, $magicLinkService, $mailer, $auditService, $config);
$approvalService = new ApprovalService($userRepository, $conversationRepository, $messageRepository, $auditService);
$adminAuthService = new AdminAuthService($auditService, $config);
$chatService = new ChatService($conversationRepository, $messageRepository, new RuleBasedChatProvider(), $auditService);

return [
    'config' => $config,
    'pdo' => $pdo,
    'services' => [
        'audit' => $auditService,
        'auth' => $authService,
        'user' => $userService,
        'magic_links' => $magicLinkService,
        'rate_limits' => $rateLimitService,
        'approval' => $approvalService,
        'admin_auth' => $adminAuthService,
        'chat' => $chatService,
    ],
];
