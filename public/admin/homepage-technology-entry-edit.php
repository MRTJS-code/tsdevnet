<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$groupRepo = $app['repositories']['homepage_technology_groups'];
$entryRepo = $app['repositories']['homepage_technology_entries'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$groups = $groupRepo->listAll();
$entry = $entryId > 0 ? $entryRepo->findById($entryId) : null;

if (!$entry) {
    $entry = [
        'id' => 0,
        'group_id' => (int) ($groups[0]['id'] ?? 0),
        'label' => '',
        'detail_text' => '',
        'sort_order' => 0,
        'is_active' => 1,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if (($_POST['form_action'] ?? 'save') === 'delete' && !$errors) {
        if ($entryId <= 0 || !$entry) {
            $errors[] = 'Technology entry not found.';
        } else {
            $entryRepo->delete($entryId);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_entry_deleted', ['technology_entry_id' => $entryId], Util::clientIp());
            header('Location: /admin/homepage-technologies.php');
            exit;
        }
    }

    $data = [
        'group_id' => (int) ($_POST['group_id'] ?? 0),
        'label' => trim((string) ($_POST['label'] ?? '')),
        'detail_text' => trim((string) ($_POST['detail_text'] ?? '')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['label'] === '') {
        $errors[] = 'Technology label is required.';
    }
    if ($data['group_id'] <= 0 || !$groupRepo->findById($data['group_id'])) {
        $errors[] = 'Choose a valid technology group.';
    }

    if (!$errors) {
        if ($entryId > 0) {
            $entryRepo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_entry_updated', ['technology_entry_id' => $entryId], Util::clientIp());
        } else {
            $entryId = $entryRepo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_entry_created', ['technology_entry_id' => $entryId], Util::clientIp());
        }

        $entry = $entryRepo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/homepage_technology_entry_edit', [
    'title' => 'Edit technology entry | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'groups' => $groups,
    'errors' => $errors,
]);
