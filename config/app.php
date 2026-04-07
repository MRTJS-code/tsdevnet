<?php
declare(strict_types=1);

use App\Support\Config;
use App\Support\Env;

$root = dirname(__DIR__);

Env::load($root . '/.env');
Env::load($root . '/.env.local');

$defaults = [
    'app_env' => Env::get('APP_ENV', 'prod'),
    'app_url' => Env::get('APP_URL', 'http://localhost:8000'),
    'app_name' => Env::get('APP_NAME', 'Tony Smith Recruiter Portal'),
    'session_secure' => Env::bool('SESSION_SECURE', false),
    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => (int) Env::get('DB_PORT', '3306'),
        'name' => Env::get('DB_NAME', 'tsdevnet'),
        'user' => Env::get('DB_USER', 'root'),
        'pass' => Env::get('DB_PASS', ''),
        'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    ],
    'turnstile' => [
        'site_key' => Env::get('TURNSTILE_SITE_KEY', ''),
        'secret_key' => Env::get('TURNSTILE_SECRET_KEY', ''),
    ],
    'mail' => [
        'from_email' => Env::get('MAIL_FROM_EMAIL', 'noreply@example.com'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Tony Smith'),
        'smtp_host' => Env::get('SMTP_HOST', 'localhost'),
        'smtp_port' => (int) Env::get('SMTP_PORT', '25'),
        'smtp_username' => Env::nullable('SMTP_USERNAME'),
        'smtp_password' => Env::nullable('SMTP_PASSWORD'),
        'smtp_secure' => Env::nullable('SMTP_SECURE'),
    ],
    'admin' => [
        'username' => Env::get('ADMIN_USERNAME', 'admin'),
        'password_hash' => Env::get('ADMIN_PASSWORD_HASH', ''),
    ],
    'magic_link_ttl' => (int) Env::get('MAGIC_LINK_TTL', '900'),
    'rate_limits' => [
        'signup_per_hour' => 8,
        'login_per_hour' => 12,
        'admin_login_per_hour' => 10,
        'chat_pending_per_day' => 5,
        'chat_approved_per_day' => 50,
    ],
];

$localConfig = [];
$localConfigFile = $root . '/config/config.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
}

return Config::merge($defaults, $localConfig);

