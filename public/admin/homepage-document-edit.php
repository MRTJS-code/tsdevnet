<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$repo = $app['repositories']['homepage_documents'];
$uploadService = $app['services']['homepage_uploads'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$entryId = (int) ($_GET['id'] ?? 0);
$errors = [];
$entry = $entryId > 0 ? $repo->findById($entryId) : null;
$allowedTypes = ['headshot', 'cv_pdf', 'module_media'];

if (!$entry) {
    $entry = [
        'id' => 0,
        'document_key' => 'hero_headshot',
        'document_type' => 'headshot',
        'title' => '',
        'description_text' => '',
        'file_path' => '',
        'external_url' => '',
        'mime_type' => '',
        'file_size_bytes' => 0,
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
            $errors[] = 'Document not found.';
        } else {
            $repo->delete($entryId);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_document_deleted', ['document_id' => $entryId, 'document_key' => $entry['document_key']], Util::clientIp());
            header('Location: /admin/homepage-documents.php');
            exit;
        }
    }

    $data = [
        'document_key' => trim((string) ($_POST['document_key'] ?? '')),
        'document_type' => trim((string) ($_POST['document_type'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'description_text' => trim((string) ($_POST['description_text'] ?? '')),
        'file_path' => (string) ($entry['file_path'] ?? ''),
        'external_url' => trim((string) ($_POST['external_url'] ?? '')),
        'mime_type' => (string) ($entry['mime_type'] ?? ''),
        'file_size_bytes' => (int) ($entry['file_size_bytes'] ?? 0),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if (!in_array($data['document_type'], $allowedTypes, true)) {
        $errors[] = 'Choose a valid document type.';
    }
    if ($data['document_key'] === '') {
        $errors[] = 'Document key is required.';
    }
    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }

    $existing = $repo->findByDocumentKey($data['document_key']);
    if ($existing && (int) $existing['id'] !== $entryId) {
        $errors[] = 'That document key already exists.';
    }

    $hasUpload = !empty($_FILES['upload_file']['name']);
    $data['external_url'] = '';
    if (!$hasUpload && $data['file_path'] === '') {
        $errors[] = 'Upload a file for this document type.';
    }

    if (!$errors && $hasUpload) {
        try {
            $upload = $uploadService->store($_FILES['upload_file'], $data['document_type']);
            $data['file_path'] = $upload['file_path'];
            $data['mime_type'] = $upload['mime_type'];
            $data['file_size_bytes'] = $upload['file_size_bytes'];
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (!$errors) {
        if ($entryId > 0) {
            $repo->update($entryId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_document_updated', ['document_id' => $entryId, 'document_key' => $data['document_key']], Util::clientIp());
        } else {
            $entryId = $repo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_document_created', ['document_id' => $entryId, 'document_key' => $data['document_key']], Util::clientIp());
        }

        $entry = $repo->findById($entryId) ?: $entry;
    } else {
        $entry = array_merge($entry, $data);
    }
}

View::render('admin/homepage_document_edit', [
    'title' => 'Edit homepage document | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entry' => $entry,
    'errors' => $errors,
    'allowedTypes' => $allowedTypes,
]);
