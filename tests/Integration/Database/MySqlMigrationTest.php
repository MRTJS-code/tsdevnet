<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/tests/TestCase.php';

final class MySqlMigrationTest extends TestCase
{
    public function run(): void
    {
        $config = $this->mysqlConfig();
        $databaseName = $this->temporaryDatabaseName($config['name']);
        $adminPdo = $this->adminPdo($config);

        try {
            $adminPdo->exec('CREATE DATABASE `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            $pdo = $this->databasePdo($config, $databaseName);
            foreach ($this->migrationFiles() as $migrationFile) {
                $sql = file_get_contents($migrationFile);
                if ($sql === false) {
                    throw new RuntimeException('Unable to read migration: ' . $migrationFile);
                }
                $pdo->exec($sql);
            }

            $this->assertTableExists($pdo, $databaseName, 'users');
            $this->assertTableExists($pdo, $databaseName, 'admin_users');
            $this->assertTableExists($pdo, $databaseName, 'assistant_knowledge');
            $this->assertTableExists($pdo, $databaseName, 'homepage_hero_settings');
            $this->assertTableExists($pdo, $databaseName, 'homepage_footer_settings');
            $this->assertTableExists($pdo, $databaseName, 'documents');
            $this->assertTableExists($pdo, $databaseName, 'homepage_modules');
            $this->assertTableExists($pdo, $databaseName, 'module_rich_text_payloads');
            $this->assertTableExists($pdo, $databaseName, 'module_timeline_entries');
            $this->assertTableExists($pdo, $databaseName, 'module_pill_card_items');
            $this->assertTableExists($pdo, $databaseName, 'module_case_study_items');
            $this->assertTableExists($pdo, $databaseName, 'module_list_items');
            $this->assertTableExists($pdo, $databaseName, 'module_quote_card_items');
            $this->assertTableExists($pdo, $databaseName, 'module_cta_banner_payloads');
            $this->assertTableExists($pdo, $databaseName, 'module_media_text_payloads');

            $this->assertIndexExists($pdo, $databaseName, 'users', 'idx_users_status_created_at');
            $this->assertIndexExists($pdo, $databaseName, 'homepage_modules', 'uniq_homepage_modules_key');
            $this->assertColumnExists($pdo, $databaseName, 'homepage_modules', 'module_type');
            $this->assertColumnExists($pdo, $databaseName, 'homepage_modules', 'display_order');
            $this->assertColumnExists($pdo, $databaseName, 'documents', 'document_type');
            $this->assertColumnExists($pdo, $databaseName, 'homepage_hero_settings', 'headshot_document_id');
            $this->assertColumnExists($pdo, $databaseName, 'homepage_footer_settings', 'cv_document_id');
            $this->assertColumnExists($pdo, $databaseName, 'module_rich_text_payloads', 'primary_cta_url');
            $this->assertColumnExists($pdo, $databaseName, 'module_media_text_payloads', 'media_position');
        } finally {
            try {
                $adminPdo->exec('DROP DATABASE IF EXISTS `' . $databaseName . '`');
            } catch (Throwable $exception) {
                throw new RuntimeException('Migration test cleanup failed for database `' . $databaseName . '`: ' . $exception->getMessage(), 0, $exception);
            }
        }
    }

    private function mysqlConfig(): array
    {
        $root = dirname(__DIR__, 3);
        $config = require $root . '/config/app.php';
        $db = $config['db'] ?? [];

        $host = (string) ($db['host'] ?? '');
        $port = (int) ($db['port'] ?? 3306);
        $user = (string) ($db['user'] ?? '');
        $pass = (string) ($db['pass'] ?? '');
        $name = (string) ($db['name'] ?? '');
        $charset = (string) ($db['charset'] ?? 'utf8mb4');

        foreach (['host' => $host, 'user' => $user, 'name' => $name, 'charset' => $charset] as $field => $value) {
            if ($value === '') {
                throw new RuntimeException('Missing MySQL config field for migration test: ' . $field);
            }
        }

        return [
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'name' => $name,
            'charset' => $charset,
        ];
    }

    private function temporaryDatabaseName(string $baseName): string
    {
        $sanitizedBase = preg_replace('/[^A-Za-z0-9_]+/', '_', $baseName) ?: 'tsdevnet';
        return $sanitizedBase . '_migtest_' . bin2hex(random_bytes(4));
    }

    private function adminPdo(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $config['host'],
            $config['port'],
            $config['charset']
        );

        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        return $pdo;
    }

    private function databasePdo(array $config, string $databaseName): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $databaseName,
            $config['charset']
        );

        return new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    private function migrationFiles(): array
    {
        $files = glob(dirname(__DIR__, 3) . '/migrations/*.sql');
        if ($files === false || $files === []) {
            throw new RuntimeException('No migration files found.');
        }
        sort($files, SORT_STRING);
        return $files;
    }

    private function assertTableExists(PDO $pdo, string $databaseName, string $tableName): void
    {
        $stmt = $pdo->prepare(
            'SELECT TABLE_NAME
             FROM information_schema.tables
             WHERE table_schema = ? AND table_name = ?
             LIMIT 1'
        );
        $stmt->execute([$databaseName, $tableName]);
        $this->assertNotNull($stmt->fetchColumn() ?: null, 'Expected table `' . $tableName . '` to exist after migrations.');
    }

    private function assertIndexExists(PDO $pdo, string $databaseName, string $tableName, string $indexName): void
    {
        $stmt = $pdo->prepare(
            'SELECT index_name
             FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?
             LIMIT 1'
        );
        $stmt->execute([$databaseName, $tableName, $indexName]);
        $this->assertNotNull($stmt->fetchColumn() ?: null, 'Expected index `' . $indexName . '` on `' . $tableName . '`.');
    }

    private function assertColumnExists(PDO $pdo, string $databaseName, string $tableName, string $columnName): void
    {
        $stmt = $pdo->prepare(
            'SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?
             LIMIT 1'
        );
        $stmt->execute([$databaseName, $tableName, $columnName]);
        $this->assertNotNull($stmt->fetchColumn() ?: null, 'Expected column `' . $columnName . '` on `' . $tableName . '`.');
    }
}
