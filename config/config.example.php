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
        'username' => 'admin',
        'password_hash' => '$2y$10$QdE6jCz1ZBz0pQ9uB6cK4O.0z0N0Q6qF9xC0G6jR7f4kD1PzWZqle',
    ],
];
