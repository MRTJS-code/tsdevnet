<?php
declare(strict_types=1);

use App\Repositories\AdminUserRepository;
use App\Repositories\AssistantKnowledgeRepository;
use App\Repositories\ContentBlockRepository;
use App\Repositories\ContentItemRepository;
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

$blocks = new ContentBlockRepository($pdo);
$items = new ContentItemRepository($pdo);
$knowledge = new AssistantKnowledgeRepository($pdo);
$admins = new AdminUserRepository($pdo);

$seedBlocks = [
    ['section_key' => 'hero', 'title' => 'A modular professional platform ready for personalised content.', 'subtitle' => 'Placeholder homepage content is active until real profile content is entered through admin.', 'body_text' => 'This project ships with neutral placeholder content so forks stay reusable and personal profile details never need to live in the repository.', 'meta_json' => json_encode(['eyebrow' => 'Professional Profile'], JSON_UNESCAPED_SLASHES), 'sort_order' => 10, 'is_active' => 1],
    ['section_key' => 'summary_cards', 'title' => 'Homepage Highlights', 'subtitle' => '', 'body_text' => '', 'meta_json' => null, 'sort_order' => 20, 'is_active' => 1],
    ['section_key' => 'about', 'title' => 'What this profile covers', 'subtitle' => 'About', 'body_text' => '', 'meta_json' => null, 'sort_order' => 30, 'is_active' => 1],
    ['section_key' => 'achievements', 'title' => 'Selected achievements', 'subtitle' => '', 'body_text' => '', 'meta_json' => json_encode(['eyebrow' => 'Highlights'], JSON_UNESCAPED_SLASHES), 'sort_order' => 40, 'is_active' => 1],
    ['section_key' => 'technology_tags', 'title' => 'Technology exposure', 'subtitle' => '', 'body_text' => '', 'meta_json' => json_encode(['eyebrow' => 'Platforms'], JSON_UNESCAPED_SLASHES), 'sort_order' => 50, 'is_active' => 1],
    ['section_key' => 'operating_approach', 'title' => 'Operating approach', 'subtitle' => '', 'body_text' => '', 'meta_json' => json_encode(['eyebrow' => 'Approach'], JSON_UNESCAPED_SLASHES), 'sort_order' => 60, 'is_active' => 1],
    ['section_key' => 'contact_cta', 'title' => 'Recruiter portal and contact', 'subtitle' => 'Contact', 'body_text' => 'Enter real contact details and calls to action through admin after setup.', 'meta_json' => json_encode(['anchor' => 'contact'], JSON_UNESCAPED_SLASHES), 'sort_order' => 70, 'is_active' => 1],
];

$seedItems = [
    'hero' => [
        ['item_key' => 'primary_cta', 'label' => 'Primary', 'title' => 'Request recruiter access', 'body_text' => '', 'link_url' => '/signup.php', 'meta_json' => json_encode(['style' => 'primary'], JSON_UNESCAPED_SLASHES), 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'secondary_cta', 'label' => 'Secondary', 'title' => 'Portal login', 'body_text' => '', 'link_url' => '/login.php', 'meta_json' => json_encode(['style' => 'ghost'], JSON_UNESCAPED_SLASHES), 'sort_order' => 20, 'is_active' => 1],
    ],
    'summary_cards' => [
        ['item_key' => 'summary_focus', 'label' => 'Focus', 'title' => 'Systems and delivery leadership', 'body_text' => 'Use admin to describe the core areas this profile should emphasise.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'summary_style', 'label' => 'Style', 'title' => 'Practical and senior', 'body_text' => 'Keep the tone credible, operational, and governance-aware.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 20, 'is_active' => 1],
        ['item_key' => 'summary_portal', 'label' => 'Portal', 'title' => 'Gated recruiter assistant', 'body_text' => 'The recruiter portal remains the secure differentiator behind signup and approval.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 30, 'is_active' => 1],
    ],
    'about' => [
        ['item_key' => 'about_one', 'label' => '', 'title' => 'Leadership and operating model', 'body_text' => 'Use this section to describe operating model, governance, and leadership themes.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'about_two', 'label' => '', 'title' => 'Systems and data', 'body_text' => 'Add platform, data, and business systems focus areas through admin.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 20, 'is_active' => 1],
        ['item_key' => 'about_three', 'label' => '', 'title' => 'Transformation delivery', 'body_text' => 'Describe the types of transformation work and delivery environments this profile should represent.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 30, 'is_active' => 1],
    ],
    'achievements' => [
        ['item_key' => 'achievement_one', 'label' => '', 'title' => 'Placeholder achievement', 'body_text' => 'Add short, outcome-oriented achievements through the CMS.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 10, 'is_active' => 1],
    ],
    'technology_tags' => [
        ['item_key' => 'tag_php', 'label' => 'PHP', 'title' => 'PHP', 'body_text' => '', 'link_url' => '', 'meta_json' => null, 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'tag_mysql', 'label' => 'MySQL', 'title' => 'MySQL', 'body_text' => '', 'link_url' => '', 'meta_json' => null, 'sort_order' => 20, 'is_active' => 1],
    ],
    'operating_approach' => [
        ['item_key' => 'approach_one', 'label' => '', 'title' => 'Credible governance', 'body_text' => 'Describe the practical governance style you want recruiters to understand.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'approach_two', 'label' => '', 'title' => 'Useful architecture', 'body_text' => 'Keep this focused on real delivery and operating outcomes.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 20, 'is_active' => 1],
        ['item_key' => 'approach_three', 'label' => '', 'title' => 'Phaseable product thinking', 'body_text' => 'Explain how work is phased and governed without overbuilding.', 'link_url' => '', 'meta_json' => null, 'sort_order' => 30, 'is_active' => 1],
    ],
    'contact_cta' => [
        ['item_key' => 'contact_primary', 'label' => 'Primary', 'title' => 'Request portal access', 'body_text' => '', 'link_url' => '/signup.php', 'meta_json' => json_encode(['style' => 'primary'], JSON_UNESCAPED_SLASHES), 'sort_order' => 10, 'is_active' => 1],
        ['item_key' => 'contact_secondary', 'label' => 'Secondary', 'title' => 'Use a magic link', 'body_text' => '', 'link_url' => '/login.php', 'meta_json' => json_encode(['style' => 'ghost'], JSON_UNESCAPED_SLASHES), 'sort_order' => 20, 'is_active' => 1],
    ],
];

$seedKnowledge = [
    ['knowledge_key' => 'role_scope', 'trigger_type' => 'contains', 'trigger_value' => 'role', 'response_text' => 'This assistant can summarise role scope, delivery accountabilities, and how the profile aligns to the brief. Approved access can be configured with deeper recruiter-facing guidance.', 'minimum_access_tier' => 'pending', 'priority' => 100, 'is_active' => 1],
    ['knowledge_key' => 'skills_stack', 'trigger_type' => 'contains', 'trigger_value' => 'skills', 'response_text' => 'Use admin to tailor this response for systems, data, delivery, and platform capability themes relevant to the profile owner.', 'minimum_access_tier' => 'pending', 'priority' => 90, 'is_active' => 1],
    ['knowledge_key' => 'leadership_style', 'trigger_type' => 'contains', 'trigger_value' => 'lead', 'response_text' => 'This rule-based response can be customised to explain leadership style, governance posture, and operating model preferences for recruiter conversations.', 'minimum_access_tier' => 'approved', 'priority' => 80, 'is_active' => 1],
];

foreach ($seedBlocks as $blockData) {
    $blockId = $blocks->upsertBySectionKey($blockData);
    if (($seedItems[$blockData['section_key']] ?? []) !== [] && $items->listByBlockId($blockId) === []) {
        foreach ($seedItems[$blockData['section_key']] as $itemData) {
            $items->create($itemData + ['block_id' => $blockId]);
        }
    }
}

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

fwrite(STDOUT, "Phase 1B placeholder content and assistant knowledge seeded.\n");
