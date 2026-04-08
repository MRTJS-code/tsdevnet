<?php
declare(strict_types=1);

// Optional local overrides. Keep real secrets in .env or environment variables.
return [
    'app_env' => 'dev',
    'app_url' => 'http://localhost:8000',
    'turnstile' => [
        'site_key' => '',
        'secret_key' => '',
    ],
    'db' => [
        'name' => 'tsdevnet',
        'user' => 'root',
        'pass' => 'password',
    ],
    'admin' => [
        'seed_email' => 'admin@example.com',
        'seed_password' => 'change-me-locally',
        'seed_name' => 'Site Admin',
    ],
];
