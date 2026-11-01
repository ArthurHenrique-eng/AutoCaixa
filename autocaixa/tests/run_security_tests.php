<?php
declare(strict_types=1);

/**
 * Testes unitários das regras de segurança que não dependem de banco nem de servidor HTTP.
 * Execução: php tests/run_security_tests.php
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

use App\Core\Ip;
use App\Core\ProductionGuard;

$passed = 0;
$failed = [];

function test_sec(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "  ok   $name\n";
    } catch (Throwable $e) {
        $failed[] = $name . ' — ' . $e->getMessage();
        echo "  FAIL $name — {$e->getMessage()}\n";
    }
}

function assert_sec(bool $cond, string $msg): void
{
    if (!$cond) throw new RuntimeException($msg);
}

function assert_throws_sec(callable $fn, string $expectedFragment): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        if (str_contains($e->getMessage(), $expectedFragment)) return;
        throw new RuntimeException("mensagem inesperada: {$e->getMessage()}");
    }
    throw new RuntimeException("esperava exceção contendo '$expectedFragment'");
}

echo "AutoCaixa Local — testes de segurança (unitários)\n";

test_sec('produção + simulated: inicialização é recusada', function () {
    assert_throws_sec(
        fn() => ProductionGuard::assertPaymentDriverAllowed('production', 'simulated'),
        'PAYMENT_DRIVER=simulated não é permitido com APP_ENV=production'
    );
});

test_sec('produção + simulated (maiúsculas/espaços): continua recusando', function () {
    assert_throws_sec(
        fn() => ProductionGuard::assertPaymentDriverAllowed(' PRODUCTION ', 'simulated'),
        'não é permitido'
    );
});

test_sec('produção + driver real: não é bloqueado por este guard', function () {
    ProductionGuard::assertPaymentDriverAllowed('production', 'cielo');
    assert_sec(true, '');
});

test_sec('ambiente local + simulated: continua funcionando normalmente', function () {
    ProductionGuard::assertPaymentDriverAllowed('local', 'simulated');
    assert_sec(true, '');
});

test_sec('ambiente de testes + simulated: continua funcionando normalmente', function () {
    ProductionGuard::assertPaymentDriverAllowed('testing', 'simulated');
    assert_sec(true, '');
});

test_sec('isProduction reconhece variações de caixa e espaço', function () {
    assert_sec(ProductionGuard::isProduction('production'), 'deveria reconhecer "production"');
    assert_sec(ProductionGuard::isProduction(' Production '), 'deveria ignorar espaços e caixa');
    assert_sec(!ProductionGuard::isProduction('local'), 'local não é produção');
    assert_sec(!ProductionGuard::isProduction(''), 'vazio não é produção');
});

test_sec('Ip::client ignora X-Forwarded-For sem proxy confiável configurado', function () {
    $_SERVER['REMOTE_ADDR'] = '203.0.113.9';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';
    assert_sec(Ip::client([]) === '203.0.113.9', 'sem proxy confiável, deve usar REMOTE_ADDR');
});

test_sec('Ip::client usa X-Forwarded-For só quando REMOTE_ADDR é um proxy confiável', function () {
    $_SERVER['REMOTE_ADDR'] = '10.0.0.5';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 10.0.0.5';
    assert_sec(Ip::client(['10.0.0.5']) === '198.51.100.1', 'deveria usar o primeiro IP do cabeçalho');
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    assert_sec(Ip::client(['10.0.0.5']) === '10.0.0.5', 'sem o cabeçalho, cai para REMOTE_ADDR mesmo sendo proxy confiável');
});

echo "\n" . count($failed) . " falha(s), $passed aprovado(s)\n";
foreach ($failed as $f) echo " - $f\n";
exit($failed === [] ? 0 : 1);
