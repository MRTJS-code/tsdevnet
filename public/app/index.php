<?php
declare(strict_types=1);

use App\Guards\UserGuard;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../../src/bootstrap.php';

$user = (new UserGuard($app['services']['auth']))->requireUser();
$tier = Util::tierFromStatus($user['status'], $app['config']);

View::render('app/dashboard', [
    'title' => 'Recruiter portal | ' . $app['config']['app_name'],
    'bodyClass' => 'page',
    'headScripts' => [
        ['src' => '/assets/js/app.js', 'defer' => true],
    ],
    'user' => $user,
    'tier' => $tier,
]);
