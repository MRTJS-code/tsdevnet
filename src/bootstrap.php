<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$configFile = $root . '/config/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo "Missing config.php. Copy config/config.example.php to config/config.php and configure.";
    exit;
}
$config = require $configFile;

if (!isset($config['app_env'])) {
    $config['app_env'] = 'prod';
}

date_default_timezone_set('UTC');
//mb_internal_encoding('UTF-8');

$secureCookie = $config['session_secure'] ?? (!empty($_SERVER['HTTPS']));
session_name('tsdevnet_sid');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once $root . '/src/Util.php';
Util::sendSecurityHeaders();

require_once $root . '/src/Db.php';
require_once $root . '/src/RateLimiter.php';
require_once $root . '/src/Turnstile.php';
require_once $root . '/src/Mailer.php';
require_once $root . '/src/Auth.php';
require_once $root . '/src/ChatService.php';

if (file_exists($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}
