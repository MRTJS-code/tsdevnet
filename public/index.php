<?php
declare(strict_types=1);

use App\Support\View;

$app = require __DIR__ . '/../src/bootstrap.php';

View::render('public/home', [
    'title' => $app['config']['app_name'],
    'metaDescription' => 'Professional profile for Tony Smith with a gated recruiter-facing portal.',
    'bodyClass' => 'page',
]);
