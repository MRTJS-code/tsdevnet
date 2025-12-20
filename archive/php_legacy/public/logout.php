<?php
require __DIR__ . '/../src/bootstrap.php';
$pdo = Db::get($config);
$auth = new Auth($pdo, $config);
$auth->logout();
header('Location: /');
exit;
