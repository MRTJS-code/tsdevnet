<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

function normalize_admin_items(array $items, array $fields): array
{
    $normalized = [];
    foreach ($items as $item) {
        $row = [];
        foreach ($fields as $field) {
            $row[$field] = (string) ($item[$field] ?? '');
        }
        $normalized[] = $row;
    }

    return $normalized;
}

function default_admin_item(array $fields): array
{
    $row = [];
    foreach ($fields as $field) {
        $row[$field] = '';
    }

    return $row;
}

function module_item_fields(string $moduleType): array
{
    return match ($moduleType) {
        'timeline' => ['title', 'subtitle', 'meta', 'summary_text', 'detail_text'],
        'pill_cards' => ['title', 'body_text', 'badge_text', 'link_label', 'link_url'],
        'case_studies' => ['title', 'category_text', 'summary_text', 'outcome_text', 'detail_text', 'link_label', 'link_url'],
        'list' => ['item_title', 'item_body', 'item_meta', 'link_label', 'link_url'],
        'quote_cards' => ['quote_text', 'attribution_name', 'attribution_role', 'attribution_context'],
        default => [],
    };
}

function structured_module_types(): array
{
    return ['timeline', 'pill_cards', 'case_studies', 'list', 'quote_cards'];
}

function load_module_items(string $moduleType, int $moduleId, object $timelineEntries, object $pillCards, object $caseStudies, object $listItems, object $quoteCards): array
{
    return match ($moduleType) {
        'timeline' => normalize_admin_items($timelineEntries->listGroupedByModuleIds([$moduleId])[$moduleId] ?? [], module_item_fields($moduleType)),
        'pill_cards' => normalize_admin_items($pillCards->listGroupedByModuleIds([$moduleId])[$moduleId] ?? [], module_item_fields($moduleType)),
        'case_studies' => normalize_admin_items($caseStudies->listGroupedByModuleIds([$moduleId])[$moduleId] ?? [], module_item_fields($moduleType)),
        'list' => normalize_admin_items($listItems->listGroupedByModuleIds([$moduleId])[$moduleId] ?? [], module_item_fields($moduleType)),
        'quote_cards' => normalize_admin_items($quoteCards->listGroupedByModuleIds([$moduleId])[$moduleId] ?? [], module_item_fields($moduleType)),
        default => [],
    };
}

function save_module_items(string $moduleType, int $moduleId, array $items, object $timelineEntries, object $pillCards, object $caseStudies, object $listItems, object $quoteCards): void
{
    match ($moduleType) {
        'timeline' => $timelineEntries->replaceForModule($moduleId, $items),
        'pill_cards' => $pillCards->replaceForModule($moduleId, $items),
        'case_studies' => $caseStudies->replaceForModule($moduleId, $items),
        'list' => $listItems->replaceForModule($moduleId, $items),
        'quote_cards' => $quoteCards->replaceForModule($moduleId, $items),
        default => null,
    };
}

function posted_item_record(array $fields): array
{
    $record = [];
    foreach ($fields as $field) {
        $record[$field] = trim((string) ($_POST['item'][$field] ?? ''));
    }

    return $record;
}

$modules = $app['repositories']['homepage_modules'];
$documents = $app['repositories']['homepage_documents'];
$richTextPayloads = $app['repositories']['module_rich_text_payloads'];
$timelineEntries = $app['repositories']['module_timeline_entries'];
$pillCards = $app['repositories']['module_pill_card_items'];
$caseStudies = $app['repositories']['module_case_study_items'];
$listItems = $app['repositories']['module_list_items'];
$quoteCards = $app['repositories']['module_quote_card_items'];
$ctaBanners = $app['repositories']['module_cta_banner_payloads'];
$mediaTextPayloads = $app['repositories']['module_media_text_payloads'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$moduleId = (int) ($_GET['id'] ?? 0);
$errors = [];
$typeOptions = $modules->allowedTypes();
$module = $moduleId > 0 ? $modules->findById($moduleId) : null;

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
        'media_document_id' => null,
        'display_order' => 0,
        'is_active' => 1,
    ];
}

$payloadState = [
    'body_text' => '',
    'primary_cta_label' => '',
    'primary_cta_url' => '',
    'secondary_cta_label' => '',
    'secondary_cta_url' => '',
    'media_position' => 'right',
    'items' => [],
];
$structuredFields = module_item_fields((string) $module['module_type']);
$itemEditor = default_admin_item($structuredFields);
$editingItemIndex = -1;

if ($moduleId > 0) {
    $payloadState = match ($module['module_type']) {
        'rich_text' => ($richTextPayloads->findByModuleId($moduleId) ?? []) + $payloadState,
        'timeline', 'pill_cards', 'case_studies', 'list', 'quote_cards' => $payloadState + ['items' => load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards)],
        'cta_banner' => ($ctaBanners->findByModuleId($moduleId) ?? []) + $payloadState,
        'media_text' => ($mediaTextPayloads->findByModuleId($moduleId) ?? []) + $payloadState,
        default => $payloadState,
    };
}

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

    if (($_POST['form_action'] ?? '') === 'edit_item' && $moduleId > 0 && in_array($module['module_type'], structured_module_types(), true)) {
        $editingItemIndex = max(-1, (int) ($_POST['item_index'] ?? -1));
        $payloadState['items'] = load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
        if (isset($payloadState['items'][$editingItemIndex])) {
            $itemEditor = $payloadState['items'][$editingItemIndex];
        }
    }

    if (($_POST['form_action'] ?? '') === 'delete_item' && $moduleId > 0 && in_array($module['module_type'], structured_module_types(), true) && !$errors) {
        $items = load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
        $deleteIndex = max(-1, (int) ($_POST['item_index'] ?? -1));
        if (isset($items[$deleteIndex])) {
            array_splice($items, $deleteIndex, 1);
            save_module_items($module['module_type'], $moduleId, $items, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'homepage_module_item_deleted', ['module_id' => $moduleId, 'module_key' => $module['module_key'], 'item_index' => $deleteIndex], Util::clientIp());
            $payloadState['items'] = load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
        }
    }

    if (($_POST['form_action'] ?? '') === 'save_item' && $moduleId > 0 && in_array($module['module_type'], structured_module_types(), true) && !$errors) {
        $fields = module_item_fields($module['module_type']);
        $record = posted_item_record($fields);
        $itemEditor = $record;
        $items = load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
        $editingItemIndex = (int) ($_POST['editing_item_index'] ?? -1);
        $hasContent = false;
        foreach ($fields as $field) {
            if ($record[$field] !== '') {
                $hasContent = true;
                break;
            }
        }
        if (!$hasContent) {
            $errors[] = 'At least one item field is required.';
        } else {
            $record['display_order'] = $editingItemIndex >= 0 ? (($editingItemIndex + 1) * 10) : ((count($items) + 1) * 10);
            $record['is_active'] = 1;
            if ($editingItemIndex >= 0 && isset($items[$editingItemIndex])) {
                $items[$editingItemIndex] = $record;
                $auditAction = 'homepage_module_item_updated';
            } else {
                $items[] = $record;
                $auditAction = 'homepage_module_item_created';
            }
            save_module_items($module['module_type'], $moduleId, $items, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, $auditAction, ['module_id' => $moduleId, 'module_key' => $module['module_key']], Util::clientIp());
            $payloadState['items'] = load_module_items($module['module_type'], $moduleId, $timelineEntries, $pillCards, $caseStudies, $listItems, $quoteCards);
            $itemEditor = default_admin_item($fields);
            $editingItemIndex = -1;
        }
    }

    if (!in_array(($_POST['form_action'] ?? ''), ['save', 'delete'], true)) {
        View::render('admin/homepage_module_edit', [
            'title' => 'Edit homepage module | ' . $app['config']['app_name'],
            'bodyClass' => 'page',
            'module' => $module,
            'payloadState' => $payloadState,
            'errors' => $errors,
            'typeOptions' => $typeOptions,
            'documents' => $documents->listAll(),
            'itemFields' => $structuredFields,
            'itemEditor' => $itemEditor,
            'editingItemIndex' => $editingItemIndex,
        ]);
        return;
    }

    $data = [
        'module_key' => trim((string) ($_POST['module_key'] ?? '')),
        'module_type' => trim((string) ($_POST['module_type'] ?? 'rich_text')),
        'eyebrow' => trim((string) ($_POST['eyebrow'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'intro_text' => trim((string) ($_POST['intro_text'] ?? '')),
        'anchor_id' => trim((string) ($_POST['anchor_id'] ?? '')),
        'style_variant' => trim((string) ($_POST['style_variant'] ?? '')),
        'media_document_id' => (int) ($_POST['media_document_id'] ?? 0),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    $payloadState = [
        'body_text' => trim((string) ($_POST['body_text'] ?? '')),
        'primary_cta_label' => trim((string) ($_POST['primary_cta_label'] ?? '')),
        'primary_cta_url' => trim((string) ($_POST['primary_cta_url'] ?? '')),
        'secondary_cta_label' => trim((string) ($_POST['secondary_cta_label'] ?? '')),
        'secondary_cta_url' => trim((string) ($_POST['secondary_cta_url'] ?? '')),
        'media_position' => trim((string) ($_POST['media_position'] ?? 'right')),
        'items' => $payloadState['items'],
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

        match ($data['module_type']) {
            'rich_text' => $richTextPayloads->upsertForModule($moduleId, $payloadState),
            'timeline' => $timelineEntries->replaceForModule($moduleId, $payloadState['items']),
            'pill_cards' => $pillCards->replaceForModule($moduleId, $payloadState['items']),
            'case_studies' => $caseStudies->replaceForModule($moduleId, $payloadState['items']),
            'list' => $listItems->replaceForModule($moduleId, $payloadState['items']),
            'quote_cards' => $quoteCards->replaceForModule($moduleId, $payloadState['items']),
            'cta_banner' => $ctaBanners->upsertForModule($moduleId, $payloadState),
            'media_text' => $mediaTextPayloads->upsertForModule($moduleId, $payloadState),
            default => null,
        };

        $module = $modules->findById($moduleId) ?: $module;
    } else {
        $module = array_merge($module, $data);
    }
}

View::render('admin/homepage_module_edit', [
    'title' => 'Edit homepage module | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'module' => $module,
    'payloadState' => $payloadState,
    'errors' => $errors,
    'typeOptions' => $typeOptions,
    'documents' => $documents->listAll(),
    'itemFields' => $structuredFields,
    'itemEditor' => $itemEditor,
    'editingItemIndex' => $editingItemIndex,
]);
