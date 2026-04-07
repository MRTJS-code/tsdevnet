<?php
declare(strict_types=1);

use App\Http\Response;
use App\Support\Util;
use App\Support\View;

$app = require __DIR__ . '/../src/bootstrap.php';

$token = (string) ($_GET['token'] ?? '');
if ($token === '') {
    View::render('auth/verify_error', [
        'title' => 'Invalid link | ' . $app['config']['app_name'],
        'message' => 'The verification token is missing.',
        'bodyClass' => 'page',
    ]);
    return;
}

$verifiedUser = $app['services']['magic_links']->consumeToken($token, Util::clientIp());
if (!$verifiedUser) {
    View::render('auth/verify_error', [
        'title' => 'Link expired | ' . $app['config']['app_name'],
        'message' => 'This login link is invalid, expired, or has already been used.',
        'bodyClass' => 'page',
    ]);
    return;
}

$app['services']['auth']->loginUser((int) $verifiedUser['id'], Util::clientIp());
Response::redirect('/app/index.php');
