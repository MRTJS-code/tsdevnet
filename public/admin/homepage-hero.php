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
    'site_title' => 'string',
    'hero_eyebrow' => 'string',
    'hero_title' => 'string',
    'hero_summary' => 'text',
    'hero_supporting_text' => 'text',
    'profile_name' => 'string',
    'profile_role' => 'string',
    'profile_location' => 'string',
    'profile_availability' => 'string',
    'open_to_work' => 'bool',
    'cta_mode' => 'string',
    'cta_primary_label' => 'string',
    'cta_primary_url' => 'url',
    'cta_secondary_label' => 'string',
    'cta_secondary_url' => 'url',
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
        $submitted[$key] = $type === 'bool' ? (!empty($_POST[$key]) ? '1' : '0') : trim((string) ($_POST[$key] ?? ''));
    }

    if ($submitted['hero_title'] === '') {
        $errors[] = 'Hero title is required.';
    }
    if ($submitted['profile_name'] === '') {
        $errors[] = 'Profile name is required.';
    }
    if ($submitted['cta_primary_label'] === '' || $submitted['cta_primary_url'] === '') {
        $errors[] = 'Primary CTA label and URL are required.';
    }

    if (!$errors) {
        $payload = [];
        foreach ($submitted as $key => $value) {
            $payload[$key] = ['value' => $value, 'type' => $fieldTypes[$key]];
        }
        $settingsRepo->upsertMany($payload);
        $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_hero_updated', ['keys' => array_keys($payload)], Util::clientIp());
        $settings = $submitted;
    } else {
        $settings = $submitted;
    }
}

View::render('admin/homepage_hero', [
    'title' => 'Homepage hero | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'settings' => $settings,
    'errors' => $errors,
]);
