<?php
// Copy this file to config.php and fill in secrets. Exclude config.php from git.
return [
    'app_env' => 'dev', // dev | prod
    'app_url' => 'https://example.com', // base URL for magic links
    'app_name' => 'Tony Smith Recruiter Portal',
    'session_secure' => false, // set true when serving over HTTPS

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'tsdevnet',
        'user' => 'root',
        'pass' => 'password',
        'charset' => 'utf8mb4',
    ],

    'turnstile' => [
        'site_key' => '',
        'secret_key' => '',
    ],

    'mail' => [
        'from_email' => 'noreply@example.com',
        'from_name' => 'Tony Smith',
        'smtp_host' => 'localhost',
        'smtp_port' => 25,
        'smtp_username' => null,
        'smtp_password' => null,
        'smtp_secure' => null, // 'tls' or 'ssl' if needed
    ],

    'admin' => [
        'user' => 'admin',
        'pass' => 'changeme',
    ],

    'magic_link_ttl' => 900, // 15 minutes
];
