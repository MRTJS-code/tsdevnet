<?php
declare(strict_types=1);

use App\Support\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/src/Support/Autoloader.php';

$config = require $root . '/config/app.php';
$pdo = Database::connect($config);

$pdo->beginTransaction();

try {
    $pdo->exec('DELETE FROM homepage_modules');
    $pdo->exec('DELETE FROM module_rich_text_payloads');
    $pdo->exec('DELETE FROM module_timeline_highlights');
    $pdo->exec('DELETE FROM module_timeline_entries');
    $pdo->exec('DELETE FROM module_pill_card_items');
    $pdo->exec('DELETE FROM module_case_study_items');
    $pdo->exec('DELETE FROM module_list_items');
    $pdo->exec('DELETE FROM module_quote_card_items');
    $pdo->exec('DELETE FROM module_cta_banner_payloads');
    $pdo->exec('DELETE FROM module_media_text_payloads');
    $pdo->exec('DELETE FROM homepage_hero_settings');
    $pdo->exec('DELETE FROM homepage_footer_settings');
    $pdo->exec('DELETE FROM documents');

    $pdo->commit();
    fwrite(STDOUT, "Homepage modular content reset complete. Auth, users, chat, audit, assistant, and legacy profile tables were left untouched.\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
