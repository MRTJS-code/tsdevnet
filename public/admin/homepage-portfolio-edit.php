<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['homepage_portfolio'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$entry = $entryId > 0 ? $repo->findById($entryId) : null;

if (!$entry) {
    $entry = [
        'id' => 0,
        'title' => '',
        'summary' => '',
        'outcome' => '',
        'link_url' => '',
        'link_label' => '',
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
        'title' => trim((string) ($_POST['title'] ?? '')),
        'summary' => trim((string) ($_POST['summary'] ?? '')),
        'outcome' => trim((string) ($_POST['outcome'] ?? '')),
        'link_url' => trim((string) ($_POST['link_url'] ?? '')),
        'link_label' => trim((string) ($_POST['link_label'] ?? '')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (!$errors) {
        if ($entryId > 0) {
            $repo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_portfolio_updated', ['portfolio_id' => $entryId], Util::clientIp());
        } else {
            $entryId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_portfolio_created', ['portfolio_id' => $entryId], Util::clientIp());
        }

        $entry = $repo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/homepage_portfolio_edit', [
    'title' => 'Edit homepage portfolio item | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'errors' => $errors,
]);
