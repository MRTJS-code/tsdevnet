<?php
declare(strict_types=1);

use App\Http\Response;
use App\Support\Util;

$app = require __DIR__ . '/../src/bootstrap.php';

$app['services']['auth']->logout(Util::clientIp());
Response::redirect('/');

return;
?>

<?php
require __DIR__ . '/../src/bootstrap.php';
$pdo = Db::get($config);
$auth = new Auth($pdo, $config);
$auth->logout();
header('Location: /');
exit;
