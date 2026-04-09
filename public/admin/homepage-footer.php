<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$settingsRepo = $app['repositories']['homepage_footer_settings'];
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
        'heading' => trim((string) ($_POST['heading'] ?? '')),
        'body_text' => trim((string) ($_POST['body_text'] ?? '')),
        'contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
        'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
        'contact_location' => trim((string) ($_POST['contact_location'] ?? '')),
        'cv_document_id' => (int) ($_POST['cv_document_id'] ?? 0),
        'linkedin_url' => trim((string) ($_POST['linkedin_url'] ?? '')),
        'github_url' => trim((string) ($_POST['github_url'] ?? '')),
    ];

    if ($submitted['contact_email'] !== '' && !Util::validateEmail($submitted['contact_email'])) {
        $errors[] = 'Contact email must be valid.';
    }

    if (!$errors) {
        $settingsRepo->replace($submitted);
        $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_footer_updated', ['keys' => array_keys($submitted)], Util::clientIp());
        $settings = $submitted;
    } else {
        $settings = $submitted;
    }
}

View::render('admin/homepage_footer', [
    'title' => 'Homepage footer | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'settings' => $settings,
    'documents' => $documents,
    'errors' => $errors,
]);
