<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$settingsRepo = $app['repositories']['homepage_hero_settings'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$errors = [];
$documents = $app['repositories']['homepage_documents']->listAll();
$settings = $settingsRepo->get() ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    $submitted = [
        'site_title' => trim((string) ($_POST['site_title'] ?? '')),
        'eyebrow' => trim((string) ($_POST['eyebrow'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'summary_text' => trim((string) ($_POST['summary_text'] ?? '')),
        'supporting_text' => trim((string) ($_POST['supporting_text'] ?? '')),
        'profile_name' => trim((string) ($_POST['profile_name'] ?? '')),
        'profile_role' => trim((string) ($_POST['profile_role'] ?? '')),
        'profile_location' => trim((string) ($_POST['profile_location'] ?? '')),
        'profile_availability' => trim((string) ($_POST['profile_availability'] ?? '')),
        'open_to_work' => !empty($_POST['open_to_work']),
        'cta_mode' => trim((string) ($_POST['cta_mode'] ?? 'register_request_chat')),
        'primary_cta_label' => trim((string) ($_POST['primary_cta_label'] ?? '')),
        'primary_cta_url' => trim((string) ($_POST['primary_cta_url'] ?? '')),
        'secondary_cta_label' => trim((string) ($_POST['secondary_cta_label'] ?? '')),
        'secondary_cta_url' => trim((string) ($_POST['secondary_cta_url'] ?? '')),
        'headshot_document_id' => (int) ($_POST['headshot_document_id'] ?? 0),
    ];

    if ($submitted['title'] === '') {
        $errors[] = 'Hero title is required.';
    }
    if ($submitted['profile_name'] === '') {
        $errors[] = 'Profile name is required.';
    }
    if ($submitted['primary_cta_label'] === '' || $submitted['primary_cta_url'] === '') {
        $errors[] = 'Primary CTA label and URL are required.';
    }

    if (!$errors) {
        $settingsRepo->replace($submitted);
        $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_hero_updated', ['keys' => array_keys($submitted)], Util::clientIp());
        $settings = $submitted;
    } else {
        $settings = $submitted;
    }
}

View::render('admin/homepage_hero', [
    'title' => 'Homepage hero | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'settings' => $settings,
    'documents' => $documents,
    'errors' => $errors,
]);
