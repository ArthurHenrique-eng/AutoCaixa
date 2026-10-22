<?php
declare(strict_types=1);

/** Testes do painel administrativo (Fase 2). Usa o banco autocaixa_test. Execução: php tests/run_admin_tests.php */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Permissions;

$pdo = Database::connect([
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'name' => 'autocaixa_test',
    'user' => getenv('DB_USER') ?: 'autocaixa',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$passed = 0;
$failed = [];

function reset_admin_db(PDO $pdo): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['audit_logs', 'stock_movements', 'sale_items', 'payments', 'sales', 'simulated_charges', 'products', 'users'] as $t) {
        $pdo->exec("TRUNCATE TABLE $t");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $hash = Auth::hashPassword('senha-correta-123');
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, active) VALUES (:n, :e, :h, :r, 1)');
    $stmt->execute([':n' => 'Admin', ':e' => 'admin@loja.test', ':h' => $hash, ':r' => 'admin']);
    $stmt->execute([':n' => 'Gerente', ':e' => 'gerente@loja.test', ':h' => $hash, ':r' => 'manager']);
    $stmt->execute([':n' => 'Operador', ':e' => 'operador@loja.test', ':h' => $hash, ':r' => 'cashier']);
}

function test(string $name, callable $fn): void
{
    global $passed, $failed, $pdo;
    try {
        reset_admin_db($pdo);
        $fn($pdo);
        $passed++;
        echo "  ok   $name\n";
    } catch (Throwable $e) {
        $failed[] = $name . ' — ' . $e->getMessage();
        echo "  FAIL $name — {$e->getMessage()}\n";
    }
}

function ok(bool $cond, string $msg): void
{
    if (!$cond) throw new RuntimeException($msg);
}

echo "AutoCaixa Local — testes do painel (Fase 2)\n";

test('login com senha correta funciona e registra acesso', function (PDO $pdo) {
    ok(Auth::attempt($pdo, 'admin@loja.test', 'senha-correta-123', '127.0.0.1') === 'ok', 'login deveria funcionar');
    $last = $pdo->query("SELECT last_login_at FROM users WHERE email = 'admin@loja.test'")->fetchColumn();
    ok($last !== null, 'último acesso deveria ser registrado');
});

test('senha errada é recusada', function (PDO $pdo) {
    ok(Auth::attempt($pdo, 'admin@loja.test', 'errada', '127.0.0.1') === 'invalid', 'senha errada deveria ser recusada');
});

test('e-mail inexistente é recusado sem revelar que não existe', function (PDO $pdo) {
    ok(Auth::attempt($pdo, 'ninguem@loja.test', 'qualquer-coisa', '127.0.0.1') === 'invalid', 'deveria recusar');
});

test('conta bloqueia após 5 falhas e não aceita nem a senha correta', function (PDO $pdo) {
    for ($i = 0; $i < Auth::MAX_FAILED; $i++) {
        Auth::attempt($pdo, 'gerente@loja.test', 'errada', '127.0.0.1');
    }
    ok(Auth::attempt($pdo, 'gerente@loja.test', 'senha-correta-123', '127.0.0.1') === 'locked', 'conta deveria estar bloqueada');
});

test('usuário inativo não entra mesmo com senha correta', function (PDO $pdo) {
    $pdo->exec("UPDATE users SET active = 0 WHERE email = 'operador@loja.test'");
    ok(Auth::attempt($pdo, 'operador@loja.test', 'senha-correta-123', '127.0.0.1') === 'invalid', 'inativo não deveria entrar');
});

test('senhas são armazenadas como hash, nunca em texto', function (PDO $pdo) {
    $hash = $pdo->query("SELECT password_hash FROM users WHERE email = 'admin@loja.test'")->fetchColumn();
    ok(str_starts_with((string) $hash, '$2y$'), 'deveria ser bcrypt');
    ok(!str_contains((string) $hash, 'senha-correta'), 'senha não pode aparecer no hash');
});

test('matriz de permissões: operador não gerencia produtos nem usuários', function () {
    ok(!Permissions::allows('cashier', 'products.manage'), 'operador não deveria gerenciar produtos');
    ok(!Permissions::allows('cashier', 'users.manage'), 'operador não deveria gerenciar usuários');
    ok(!Permissions::allows('cashier', 'sales.cancel'), 'operador não deveria cancelar vendas');
    ok(Permissions::allows('cashier', 'sales.view'), 'operador deveria ver vendas');
});

test('matriz de permissões: gerente gerencia produtos e estoque, mas não usuários', function () {
    ok(Permissions::allows('manager', 'products.manage'), 'gerente deveria gerenciar produtos');
    ok(Permissions::allows('manager', 'stock.adjust'), 'gerente deveria ajustar estoque');
    ok(!Permissions::allows('manager', 'users.manage'), 'gerente não deveria gerenciar usuários');
    ok(!Permissions::allows('manager', 'audit.view'), 'gerente não deveria ver auditoria');
});

test('perfil desconhecido ou nulo não recebe nenhuma permissão', function () {
    ok(!Permissions::allows(null, 'dashboard.view'), 'nulo sem permissão');
    ok(!Permissions::allows('hacker', 'dashboard.view'), 'perfil inventado sem permissão');
});

test('auditoria remove campos sensíveis antes de gravar', function (PDO $pdo) {
    Audit::log($pdo, null, 'teste', 'auth', null, ['email' => 'x@y.z', 'password' => 'segredo', 'card_number' => '4111'], '127.0.0.1');
    $details = (string) $pdo->query("SELECT details FROM audit_logs ORDER BY id DESC LIMIT 1")->fetchColumn();
    ok(str_contains($details, 'x@y.z'), 'campo permitido deveria ficar');
    ok(!str_contains($details, 'segredo'), 'senha não pode ir para a auditoria');
    ok(!str_contains($details, '4111'), 'dado de cartão não pode ir para a auditoria');
});

test('login bem-sucedido e falhas ficam registrados na auditoria', function (PDO $pdo) {
    Auth::attempt($pdo, 'admin@loja.test', 'errada', '127.0.0.1');
    Auth::attempt($pdo, 'admin@loja.test', 'senha-correta-123', '127.0.0.1');
    $actions = $pdo->query("SELECT action FROM audit_logs ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    ok($actions === ['login_failed', 'login'], 'deveria registrar falha e depois o login: ' . implode(',', $actions));
});

echo "\n" . count($failed) . " falha(s), $passed aprovado(s)\n";
foreach ($failed as $f) echo " - $f\n";
exit($failed === [] ? 0 : 1);
