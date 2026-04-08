<?php
declare(strict_types=1);

use App\Guards\AdminGuard;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

(new AdminGuard($app['services']['admin_auth']))->requireAdmin();

View::render('admin/homepage_certifications', [
    'title' => 'Homepage certifications | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'entries' => $app['repositories']['homepage_certifications']->listAll(),
]);
