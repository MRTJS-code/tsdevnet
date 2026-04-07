<?php
declare(strict_types=1);

use App\Http\Response;
use App\Support\Util;

$app = require __DIR__ . '/../../src/bootstrap.php';

$app['services']['admin_auth']->logout(Util::clientIp());
Response::redirect('/admin/login.php');
