<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

View::render('admin/homepage_technologies', [
    'title' => 'Homepage technologies | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'groups' => $app['repositories']['homepage_technology_groups']->listAll(),
    'entries' => $app['repositories']['homepage_technology_entries']->listAllWithGroup(),
]);
