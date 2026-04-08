<?php
declare(strict_types=1);

use App\Support\View;

$app = require __DIR__ . '/../src/bootstrap.php';
$homepage = $app['services']['site_content']->homepage();

View::render('public/home_modular', [
    'title' => $app['config']['app_name'],
    'metaDescription' => 'Professional profile website with a gated recruiter-facing portal.',
    'bodyClass' => 'page',
    'homepage' => $homepage,
]);
