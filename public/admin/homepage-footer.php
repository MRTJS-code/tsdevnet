<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$settingsRepo = $app['repositories']['site_settings'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$errors = [];
$fieldTypes = [
    'footer_heading' => 'string',
    'footer_body' => 'text',
    'contact_email' => 'email',
    'contact_phone' => 'tel',
    'contact_location' => 'string',
    'linkedin_url' => 'url',
    'github_url' => 'url',
];

$settings = [];
foreach ($settingsRepo->getAllIndexed() as $row) {
    $settings[$row['setting_key']] = (string) ($row['setting_value_text'] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    $submitted = [];
    foreach ($fieldTypes as $key => $type) {
        $submitted[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if ($submitted['contact_email'] !== '' && !Util::validateEmail($submitted['contact_email'])) {
        $errors[] = 'Contact email must be valid.';
    }

    if (!$errors) {
        $payload = [];
        foreach ($submitted as $key => $value) {
            $payload[$key] = ['value' => $value, 'type' => $fieldTypes[$key]];
        }
        $settingsRepo->upsertMany($payload);
        $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_footer_updated', ['keys' => array_keys($payload)], Util::clientIp());
        $settings = $submitted;
    } else {
        $settings = $submitted;
    }
}

View::render('admin/homepage_footer', [
    'title' => 'Homepage footer | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'settings' => $settings,
    'errors' => $errors,
]);
