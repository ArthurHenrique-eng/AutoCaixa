<?php
declare(strict_types=1);

// Aplica apenas migrações ainda não registradas. Uso: php scripts/migrate.php
$config = require dirname(__DIR__) . '/config/config.php';
$db = $config['db'];
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS schema_migrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_schema_migrations_migration (migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$files = glob($migrationsDir . '/*.sql') ?: [];
sort($files, SORT_NATURAL);

$appliedStmt = $pdo->query('SELECT migration FROM schema_migrations');
$applied = array_fill_keys($appliedStmt->fetchAll(PDO::FETCH_COLUMN), true);

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = :table
         LIMIT 1'
    );
    $stmt->execute(['table' => $table]);
    return $stmt->fetchColumn() !== false;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = :table
           AND column_name = :column
         LIMIT 1'
    );
    $stmt->execute([
        'table' => $table,
        'column' => $column,
    ]);
    return $stmt->fetchColumn() !== false;
}

function recordMigration(PDO $pdo, string $migration): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO schema_migrations (migration) VALUES (:migration)'
    );
    $stmt->execute(['migration' => $migration]);
}

foreach ($files as $file) {
    $migration = basename($file);

    if (isset($applied[$migration])) {
        echo "Ignorando {$migration} (já aplicada)\n";
        continue;
    }

    // Baseline para bancos existentes antes da adoção do controle de versões.
    // Não recria nem altera tabelas que já estejam presentes.
    if ($migration === '001_create_schema.sql') {
        if (
            tableExists($pdo, 'products')
            && tableExists($pdo, 'sales')
            && tableExists($pdo, 'users')
        ) {
            recordMigration($pdo, $migration);
            $applied[$migration] = true;
            echo "Registrando {$migration} (baseline existente)\n";
            continue;
        }
    }

    if ($migration === '002_admin_users_audit.sql') {
        if (
            tableExists($pdo, 'users')
            && tableExists($pdo, 'audit_logs')
            && columnExists($pdo, 'stock_movements', 'user_id')
        ) {
            recordMigration($pdo, $migration);
            $applied[$migration] = true;
            echo "Registrando {$migration} (baseline existente)\n";
            continue;
        }
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Não foi possível ler a migração: {$migration}");
    }

    echo "Aplicando {$migration}\n";

    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);
        recordMigration($pdo, $migration);
        $pdo->commit();
        $applied[$migration] = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

echo "Migrações concluídas.\n";
