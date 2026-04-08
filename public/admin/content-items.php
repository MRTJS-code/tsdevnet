<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\Security;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$blockId = (int) ($_GET['block_id'] ?? 0);
$blockRepo = $app['repositories']['content_blocks'];
$itemRepo = $app['repositories']['content_items'];
$audit = $app['services']['audit'];
$admin = $app['services']['admin_auth']->currentAdmin();
$selectedBlock = $blockId > 0 ? $blockRepo->findById($blockId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceSameOrigin($app['config']);
    if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }

    $itemId = (int) ($_POST['item_id'] ?? 0);
    $item = $itemRepo->findById($itemId);
    if ($item) {
        $itemRepo->delete($itemId);
        $audit->log('admin', $admin ? (int) $admin['id'] : null, 'content_item_deleted', ['item_id' => $itemId, 'block_id' => (int) $item['block_id']], Util::clientIp());
    }

    $redirectBlockId = (int) ($_POST['redirect_block_id'] ?? 0);
    header('Location: /admin/content-items.php' . ($redirectBlockId > 0 ? '?block_id=' . $redirectBlockId : ''));
    exit;
}

$items = $blockId > 0 ? $itemRepo->listByBlockId($blockId) : $itemRepo->listAllWithBlock();

View::render('admin/content_items', [
    'title' => 'Homepage items | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'items' => $items,
    'selectedBlock' => $selectedBlock,
]);
