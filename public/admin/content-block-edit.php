<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Json;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$blocks = $app['repositories']['content_blocks'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$blockId = (int) ($_GET['id'] ?? 0);
$errors = [];
$sectionOptions = ['hero', 'summary_cards', 'about', 'achievements', 'technology_tags', 'operating_approach', 'contact_cta'];
$block = $blockId > 0 ? $blocks->findById($blockId) : null;

if (!$block) {
    $block = [
        'id' => 0,
        'section_key' => 'hero',
        'title' => '',
        'subtitle' => '',
        'body_text' => '',
        'meta_json' => '',
        'sort_order' => 0,
        'is_active' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    $metaJson = Json::encodeArray($_POST['meta_json'] ?? null);
    if (trim((string) ($_POST['meta_json'] ?? '')) !== '' && $metaJson === null) {
        $errors[] = 'Metadata JSON must be a valid object or array.';
    }

    $data = [
        'section_key' => trim((string) ($_POST['section_key'] ?? 'hero')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'subtitle' => trim((string) ($_POST['subtitle'] ?? '')),
        'body_text' => trim((string) ($_POST['body_text'] ?? '')),
        'meta_json' => $metaJson,
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if (!in_array($data['section_key'], $sectionOptions, true)) {
        $errors[] = 'Invalid section key.';
    }
    $existing = $blocks->findBySectionKey($data['section_key']);
    if ($existing && (int) $existing['id'] !== $blockId) {
        $errors[] = 'That section key already exists. Edit the existing block instead of creating a duplicate.';
    }

    if (!$errors) {
        if ($blockId > 0) {
            $blocks->update($blockId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_block_updated', ['block_id' => $blockId, 'section_key' => $data['section_key']], Util::clientIp());
        } else {
            $blockId = $blocks->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_block_created', ['block_id' => $blockId, 'section_key' => $data['section_key']], Util::clientIp());
        }

        $block = $blocks->findById($blockId) ?: $block;
    } else {
        $block = array_merge($block, $data, ['meta_json' => (string) ($_POST['meta_json'] ?? '')]);
    }
}

View::render('admin/content_block_edit', [
    'title' => 'Edit homepage block | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'block' => $block,
    'sectionOptions' => $sectionOptions,
    'errors' => $errors,
]);
