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
    $pdo->exec('DELETE FROM module_rich_text_sections');
    $pdo->exec('DELETE FROM homepage_modules');
    $pdo->exec('DELETE FROM content_items');
    $pdo->exec('DELETE FROM content_blocks');
    $pdo->exec('DELETE FROM profile_experience_highlights');
    $pdo->exec('DELETE FROM profile_experience');
    $pdo->exec('DELETE FROM profile_certifications');
    $pdo->exec('DELETE FROM profile_technologies');
    $pdo->exec('DELETE FROM profile_technology_groups');
    $pdo->exec('DELETE FROM portfolio_items');
    $pdo->exec('DELETE FROM testimonials');
    $pdo->exec('DELETE FROM site_settings');
    $pdo->exec('DELETE FROM documents');

    $pdo->commit();
    fwrite(STDOUT, "Homepage/profile content reset complete. Auth, users, chat, audit, and assistant tables were left untouched.\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
