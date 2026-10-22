<?php
declare(strict_types=1);

// Aplica as migrações SQL em ordem. Uso: php scripts/migrate.php
$config = require dirname(__DIR__) . '/config/config.php';
$db = $config['db'];
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

foreach (glob(dirname(__DIR__) . '/database/migrations/*.sql') as $file) {
    echo 'Aplicando ' . basename($file) . "\n";
    $pdo->exec((string) file_get_contents($file));
}
echo "Migrações concluídas.\n";
