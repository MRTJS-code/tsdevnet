<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['homepage_experience'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$entry = $entryId > 0 ? $repo->findById($entryId) : null;

if (!$entry) {
    $entry = [
        'id' => 0,
        'role_title' => '',
        'organisation' => '',
        'period_label' => '',
        'summary' => '',
        'highlight_text' => '',
        'sort_order' => 0,
        'is_active' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    $data = [
        'role_title' => trim((string) ($_POST['role_title'] ?? '')),
        'organisation' => trim((string) ($_POST['organisation'] ?? '')),
        'period_label' => trim((string) ($_POST['period_label'] ?? '')),
        'summary' => trim((string) ($_POST['summary'] ?? '')),
        'highlight_text' => trim((string) ($_POST['highlight_text'] ?? '')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['role_title'] === '' || $data['organisation'] === '' || $data['period_label'] === '') {
        $errors[] = 'Role title, organisation, and period label are required.';
    }

    if (!$errors) {
        if ($entryId > 0) {
            $repo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_experience_updated', ['experience_id' => $entryId], Util::clientIp());
        } else {
            $entryId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_experience_created', ['experience_id' => $entryId], Util::clientIp());
        }

        $entry = $repo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/homepage_experience_edit', [
    'title' => 'Edit homepage experience | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'errors' => $errors,
]);
