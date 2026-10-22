<?php
declare(strict_types=1);

/**
 * Cria o primeiro administrador. A senha vem da variável de ambiente ADMIN_PASSWORD, nunca de argumentos,
 * para não ficar no histórico do shell.
 * Uso: ADMIN_EMAIL=dono@loja.com ADMIN_NAME="Nome" ADMIN_PASSWORD='senha-com-12+' php scripts/create_admin.php
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

use App\Core\Auth;
use App\Core\Database;

$config = require dirname(__DIR__) . '/config/config.php';
$email = strtolower((string) getenv('ADMIN_EMAIL'));
$name = (string) getenv('ADMIN_NAME');
$password = (string) getenv('ADMIN_PASSWORD');

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $name === '') {
    fwrite(STDERR, "Defina ADMIN_EMAIL e ADMIN_NAME.\n");
    exit(1);
}
if (strlen($password) < 12) {
    fwrite(STDERR, "ADMIN_PASSWORD deve ter pelo menos 12 caracteres.\n");
    exit(1);
}

$pdo = Database::connect($config['db']);
$pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:n, :e, :h, "admin")')
    ->execute([':n' => $name, ':e' => $email, ':h' => Auth::hashPassword($password)]);
echo "Administrador criado: $email\n";
