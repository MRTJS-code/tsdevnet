<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

View::render('admin/homepage_modules', [
    'title' => 'Homepage modules | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'modules' => $app['repositories']['homepage_modules']->listAll(),
]);
