<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$modules = $app['repositories']['homepage_modules'];
$richTextSections = $app['repositories']['module_rich_text_sections'];
$documents = $app['repositories']['homepage_documents'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$moduleId = (int) ($_GET['id'] ?? 0);
$errors = [];
$typeOptions = $modules->allowedTypes();
$module = $moduleId > 0 ? $modules->findById($moduleId) : null;
$payload = $moduleId > 0 ? ($richTextSections->findByModuleId($moduleId) ?? []) : [];

if (!$module) {
    $module = [
        'id' => 0,
        'module_key' => '',
        'module_type' => 'rich_text',
        'eyebrow' => '',
        'title' => '',
        'intro_text' => '',
        'anchor_id' => '',
        'style_variant' => '',
        'group_key' => '',
        'media_document_id' => null,
        'display_order' => 0,
        'is_active' => 1,
    ];
}

$isInlineRichText = in_array($module['module_type'], ['rich_text', 'cta_info'], true);
$payload += [
    'body_text' => '',
    'cta_label' => '',
    'cta_url' => '',
    'secondary_cta_label' => '',
    'secondary_cta_url' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if (($_POST['form_action'] ?? 'save') === 'delete' && !$errors) {
        if ($moduleId <= 0 || !$module) {
            $errors[] = 'Module not found.';
        } else {
            $modules->delete($moduleId);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_module_deleted', ['module_id' => $moduleId, 'module_key' => $module['module_key']], Util::clientIp());
            header('Location: /admin/homepage-modules.php');
            exit;
        }
    }

    $data = [
        'module_key' => trim((string) ($_POST['module_key'] ?? '')),
        'module_type' => trim((string) ($_POST['module_type'] ?? 'rich_text')),
        'eyebrow' => trim((string) ($_POST['eyebrow'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'intro_text' => trim((string) ($_POST['intro_text'] ?? '')),
        'anchor_id' => trim((string) ($_POST['anchor_id'] ?? '')),
        'style_variant' => trim((string) ($_POST['style_variant'] ?? '')),
        'group_key' => trim((string) ($_POST['group_key'] ?? '')),
        'media_document_id' => (int) ($_POST['media_document_id'] ?? 0),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    $payloadData = [
        'body_text' => trim((string) ($_POST['body_text'] ?? '')),
        'cta_label' => trim((string) ($_POST['cta_label'] ?? '')),
        'cta_url' => trim((string) ($_POST['cta_url'] ?? '')),
        'secondary_cta_label' => trim((string) ($_POST['secondary_cta_label'] ?? '')),
        'secondary_cta_url' => trim((string) ($_POST['secondary_cta_url'] ?? '')),
    ];

    if ($data['module_key'] === '') {
        $errors[] = 'Module key is required.';
    }
    if (!in_array($data['module_type'], $typeOptions, true)) {
        $errors[] = 'Choose a valid module type.';
    }
    if ($data['title'] === '') {
        $errors[] = 'Module title is required.';
    }

    $existing = $modules->findByModuleKey($data['module_key']);
    if ($existing && (int) $existing['id'] !== $moduleId) {
        $errors[] = 'That module key already exists.';
    }

    if (!$errors) {
        if ($moduleId > 0) {
            $modules->update($moduleId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_module_updated', ['module_id' => $moduleId, 'module_key' => $data['module_key']], Util::clientIp());
        } else {
            $moduleId = $modules->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_module_created', ['module_id' => $moduleId, 'module_key' => $data['module_key']], Util::clientIp());
        }

        if (in_array($data['module_type'], ['rich_text', 'cta_info'], true)) {
            $richTextSections->upsertForModule($moduleId, $payloadData);
        }

        $module = $modules->findById($moduleId) ?: $module;
        $payload = in_array($module['module_type'], ['rich_text', 'cta_info'], true)
            ? ($richTextSections->findByModuleId($moduleId) ?? $payloadData)
            : $payloadData;
        $isInlineRichText = in_array($module['module_type'], ['rich_text', 'cta_info'], true);
    } else {
        $module = array_merge($module, $data);
        $payload = array_merge($payload, $payloadData);
        $isInlineRichText = in_array($data['module_type'], ['rich_text', 'cta_info'], true);
    }
}

View::render('admin/homepage_module_edit', [
    'title' => 'Edit homepage module | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'module' => $module,
    'payload' => $payload,
    'errors' => $errors,
    'typeOptions' => $typeOptions,
    'documents' => $documents->listAll(),
    'payloadEditorPath' => Util::homepageModuleEditorPath((string) $module['module_type']),
    'isInlineRichText' => $isInlineRichText,
]);
