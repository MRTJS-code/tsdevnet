<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['assistant_knowledge'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$entry = $entryId > 0 ? $repo->findById($entryId) : null;

if (!$entry) {
    $entry = [
        'id' => 0,
        'knowledge_key' => '',
        'trigger_type' => 'contains',
        'trigger_value' => '',
        'response_text' => '',
        'minimum_access_tier' => 'pending',
        'priority' => 0,
        'is_active' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    $data = [
        'knowledge_key' => trim((string) ($_POST['knowledge_key'] ?? '')),
        'trigger_type' => trim((string) ($_POST['trigger_type'] ?? 'contains')),
        'trigger_value' => trim((string) ($_POST['trigger_value'] ?? '')),
        'response_text' => trim((string) ($_POST['response_text'] ?? '')),
        'minimum_access_tier' => trim((string) ($_POST['minimum_access_tier'] ?? 'pending')),
        'priority' => (int) ($_POST['priority'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['knowledge_key'] === '' || $data['trigger_value'] === '' || $data['response_text'] === '') {
        $errors[] = 'Knowledge key, trigger value, and response text are required.';
    }
    if (!in_array($data['trigger_type'], ['contains', 'exact'], true)) {
        $errors[] = 'Invalid trigger type.';
    }
    if (!in_array($data['minimum_access_tier'], ['pending', 'approved'], true)) {
        $errors[] = 'Invalid minimum access tier.';
    }

    if (!$errors) {
        if ($entryId > 0) {
            $repo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'assistant_knowledge_updated', ['knowledge_id' => $entryId, 'knowledge_key' => $data['knowledge_key']], Util::clientIp());
        } else {
            $entryId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'assistant_knowledge_created', ['knowledge_id' => $entryId, 'knowledge_key' => $data['knowledge_key']], Util::clientIp());
        }

        $entry = $repo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/assistant_knowledge_edit', [
    'title' => 'Edit assistant knowledge | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'errors' => $errors,
]);
