<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

$blockId = (int) ($_GET['block_id'] ?? 0);
$blockRepo = $app['repositories']['content_blocks'];
$itemRepo = $app['repositories']['content_items'];
$selectedBlock = $blockId > 0 ? $blockRepo->findById($blockId) : null;
$items = $blockId > 0 ? $itemRepo->listByBlockId($blockId) : $itemRepo->listAllWithBlock();

View::render('admin/content_items', [
    'title' => 'Homepage items | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'items' => $items,
    'selectedBlock' => $selectedBlock,
]);

