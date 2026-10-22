<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Conexão única com MySQL via PDO, com exceções e sem emulação de prepared statements. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $cfg): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $cfg['host'], $cfg['name'], $cfg['charset']);
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    /** Usado apenas pelos testes para injetar uma conexão de teste. */
    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
