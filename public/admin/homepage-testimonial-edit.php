<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['homepage_testimonials'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$entry = $entryId > 0 ? $repo->findById($entryId) : null;

if (!$entry) {
    $entry = [
        'id' => 0,
        'quote_text' => '',
        'person_name' => '',
        'person_title' => '',
        'organisation' => '',
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
        'quote_text' => trim((string) ($_POST['quote_text'] ?? '')),
        'person_name' => trim((string) ($_POST['person_name'] ?? '')),
        'person_title' => trim((string) ($_POST['person_title'] ?? '')),
        'organisation' => trim((string) ($_POST['organisation'] ?? '')),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['quote_text'] === '' || $data['person_name'] === '') {
        $errors[] = 'Quote text and person name are required.';
    }

    if (!$errors) {
        if ($entryId > 0) {
            $repo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_testimonial_updated', ['testimonial_id' => $entryId], Util::clientIp());
        } else {
            $entryId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_testimonial_created', ['testimonial_id' => $entryId], Util::clientIp());
        }

        $entry = $repo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/homepage_testimonial_edit', [
    'title' => 'Edit homepage testimonial | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'errors' => $errors,
]);
