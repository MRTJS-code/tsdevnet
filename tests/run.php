<?php
declare(strict_types=1);

$testFiles = [
    __DIR__ . '/Unit/Repositories/HomepageModuleRepositoryTest.php',
    __DIR__ . '/Unit/Services/HomepageContentServiceTest.php',
];

$runMySqlMigrations = in_array('--mysql-migrations', $argv, true)
    || getenv('TSDEVNET_RUN_MYSQL_MIGRATIONS') === '1';

if ($runMySqlMigrations) {
    $testFiles[] = __DIR__ . '/Integration/Database/MySqlMigrationTest.php';
}

$failures = 0;

foreach ($testFiles as $testFile) {
    require_once $testFile;
}

$tests = [
    new HomepageModuleRepositoryTest(),
    new HomepageContentServiceTest(),
];

if ($runMySqlMigrations) {
    $tests[] = new MySqlMigrationTest();
}

foreach ($tests as $test) {
    try {
        $test->run();
        fwrite(STDOUT, '[PASS] ' . $test::class . PHP_EOL);
    } catch (Throwable $exception) {
        $failures++;
        fwrite(STDERR, '[FAIL] ' . $test::class . ': ' . $exception->getMessage() . PHP_EOL);
    }
}

exit($failures === 0 ? 0 : 1);
