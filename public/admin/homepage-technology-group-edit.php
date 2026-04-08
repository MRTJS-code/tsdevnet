<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['homepage_technology_groups'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$groupId = (int) ($_GET['id'] ?? 0);
$errors = [];
$group = $groupId > 0 ? $repo->findById($groupId) : null;
$allowedKeys = ['core_strengths', 'supporting_tools', 'exposure_familiarity'];

if (!$group) {
    $group = [
        'id' => 0,
        'group_key' => 'core_strengths',
        'title' => '',
        'intro_text' => '',
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
        if ($groupId <= 0 || !$group) {
            $errors[] = 'Technology group not found.';
        } else {
            $repo->delete($groupId);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_group_deleted', ['group_id' => $groupId], Util::clientIp());
            header('Location: /admin/homepage-technologies.php');
            exit;
        }
    }

    $data = [
        'group_key' => trim((string) ($_POST['group_key'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'intro_text' => trim((string) ($_POST['intro_text'] ?? '')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if (!in_array($data['group_key'], $allowedKeys, true)) {
        $errors[] = 'Choose a valid technology group key.';
    }
    if ($data['title'] === '') {
        $errors[] = 'Group title is required.';
    }

    $existing = $repo->findByGroupKey($data['group_key']);
    if ($existing && (int) $existing['id'] !== $groupId) {
        $errors[] = 'That group key already exists.';
    }

    if (!$errors) {
        if ($groupId > 0) {
            $repo->update($groupId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_group_updated', ['group_id' => $groupId], Util::clientIp());
        } else {
            $groupId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_technology_group_created', ['group_id' => $groupId], Util::clientIp());
        }

        $group = $repo->findById($groupId) ?: $group;
    } else {
        $group = array_merge($group, $data);
    }
}

View::render('admin/homepage_technology_group_edit', [
    'title' => 'Edit technology group | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'group' => $group,
    'errors' => $errors,
    'allowedKeys' => $allowedKeys,
]);
