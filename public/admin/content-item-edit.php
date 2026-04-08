<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Json;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$blockRepo = $app['repositories']['content_blocks'];
$itemRepo = $app['repositories']['content_items'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$blocks = $blockRepo->listAll();
$itemId = (int) ($_GET['id'] ?? 0);
$prefillBlockId = (int) ($_GET['block_id'] ?? 0);
$errors = [];
$item = $itemId > 0 ? $itemRepo->findById($itemId) : null;

if (!$item) {
    $item = [
        'id' => 0,
        'block_id' => $prefillBlockId > 0 ? $prefillBlockId : ((int) ($blocks[0]['id'] ?? 0)),
        'item_key' => '',
        'label' => '',
        'title' => '',
        'body_text' => '',
        'link_url' => '',
        'meta_json' => '',
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
        if ($itemId <= 0 || !$item) {
            $errors[] = 'Item not found.';
        } else {
            $deletedBlockId = (int) $item['block_id'];
            $itemRepo->delete($itemId);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_item_deleted', ['item_id' => $itemId, 'block_id' => $deletedBlockId], Util::clientIp());
            header('Location: /admin/content-items.php?block_id=' . $deletedBlockId);
            exit;
        }
    }

    $metaJson = Json::encodeArray($_POST['meta_json'] ?? null);
    if (trim((string) ($_POST['meta_json'] ?? '')) !== '' && $metaJson === null) {
        $errors[] = 'Metadata JSON must be a valid object or array.';
    }

    $data = [
        'block_id' => (int) ($_POST['block_id'] ?? 0),
        'item_key' => trim((string) ($_POST['item_key'] ?? '')),
        'label' => trim((string) ($_POST['label'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'body_text' => trim((string) ($_POST['body_text'] ?? '')),
        'link_url' => trim((string) ($_POST['link_url'] ?? '')),
        'meta_json' => $metaJson,
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'is_active' => !empty($_POST['is_active']),
    ];

    if ($data['block_id'] <= 0 || !$blockRepo->findById($data['block_id'])) {
        $errors[] = 'Choose a valid content block.';
    }

    if (!$errors) {
        if ($itemId > 0) {
            $itemRepo->update($itemId, $data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_item_updated', ['item_id' => $itemId, 'block_id' => $data['block_id']], Util::clientIp());
        } else {
            $itemId = $itemRepo->create($data);
            $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_item_created', ['item_id' => $itemId, 'block_id' => $data['block_id']], Util::clientIp());
        }

        $item = $itemRepo->findById($itemId) ?: $item;
    } else {
        $item = array_merge($item, $data, ['meta_json' => (string) ($_POST['meta_json'] ?? '')]);
    }
}

View::render('admin/content_item_edit', [
    'title' => 'Edit homepage item | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'item' => $item,
    'blocks' => $blocks,
    'errors' => $errors,
]);
