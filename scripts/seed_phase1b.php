<?php
declare(strict_types=1);

use App\Repositories\AdminUserRepository;
use App\Repositories\AssistantKnowledgeRepository;
use App\Support\Database;
use App\Support\Util;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/src/Support/Autoloader.php';

$config = require $root . '/config/app.php';
$pdo = Database::connect($config);

$knowledge = new AssistantKnowledgeRepository($pdo);
$admins = new AdminUserRepository($pdo);

$seedKnowledge = [
    ['knowledge_key' => 'role_scope', 'trigger_type' => 'contains', 'trigger_value' => 'role', 'response_text' => 'This assistant can summarise role scope, delivery accountabilities, and how the profile aligns to the brief. Approved access can be configured with deeper recruiter-facing guidance.', 'minimum_access_tier' => 'pending', 'priority' => 100, 'is_active' => 1],
    ['knowledge_key' => 'skills_stack', 'trigger_type' => 'contains', 'trigger_value' => 'skills', 'response_text' => 'Use admin to tailor this response for systems, data, delivery, and platform capability themes relevant to the profile owner.', 'minimum_access_tier' => 'pending', 'priority' => 90, 'is_active' => 1],
    ['knowledge_key' => 'leadership_style', 'trigger_type' => 'contains', 'trigger_value' => 'lead', 'response_text' => 'This rule-based response can be customised to explain leadership style, governance posture, and operating model preferences for recruiter conversations.', 'minimum_access_tier' => 'approved', 'priority' => 80, 'is_active' => 1],
];

$existingKnowledge = [];
foreach ($knowledge->listAll() as $entry) {
    $existingKnowledge[$entry['knowledge_key']] = true;
}
foreach ($seedKnowledge as $entry) {
    if (!isset($existingKnowledge[$entry['knowledge_key']])) {
        $knowledge->create($entry);
    }
}

if ($admins->countActive() === 0) {
    $email = trim((string) ($config['admin']['seed_email'] ?? ''));
    $password = (string) ($config['admin']['seed_password'] ?? '');
    $name = trim((string) ($config['admin']['seed_name'] ?? 'Site Admin'));

    if ($email === '') {
        $email = trim((string) readline('Admin email: '));
    }
    if ($password === '') {
        $password = (string) readline('Admin password: ');
    }
    if ($name === '') {
        $name = 'Site Admin';
    }

    if (!Util::validateEmail($email) || $password === '') {
        fwrite(STDERR, "A valid admin email and password are required to seed the initial admin user.\n");
        exit(1);
    }

    $admins->create(strtolower($email), password_hash($password, PASSWORD_DEFAULT), $name);
    fwrite(STDOUT, "Initial admin user created for {$email}\n");
} else {
    fwrite(STDOUT, "Admin users already exist. Skipping admin seed.\n");
}

fwrite(STDOUT, "Phase 1B admin bootstrap and generic assistant knowledge seeded.\n");
