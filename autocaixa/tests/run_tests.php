<?php
declare(strict_types=1);

/**
 * Testes automatizados da Fase 1. Usam banco separado (autocaixa_test) e nunca cobram nada real.
 * Execução: php tests/run_tests.php
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

use App\Core\Database;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Payments\SimulatedPaymentGateway;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\CheckoutService;

$cfg = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'name' => 'autocaixa_test',
    'user' => getenv('DB_USER') ?: 'autocaixa',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
$pdo = Database::connect($cfg);

$passed = 0;
$failed = [];

function reset_db(PDO $pdo): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['stock_movements', 'sale_items', 'payments', 'sales', 'simulated_charges', 'products'] as $t) {
        $pdo->exec("TRUNCATE TABLE $t");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $pdo->exec("INSERT INTO products (barcode, name, price_cents, stock, min_stock, active) VALUES
        ('100', 'Café 500 g', 1890, 10, 0, 1),
        ('200', 'Leite 1 L', 549, 5, 0, 1),
        ('300', 'Produto inativo', 999, 10, 0, 0)");
}

function test(string $name, callable $fn): void
{
    global $passed, $failed, $pdo;
    try {
        reset_db($pdo);
        $fn($pdo);
        $passed++;
        echo "  ok   $name\n";
    } catch (Throwable $e) {
        $failed[] = $name . ' — ' . $e->getMessage();
        echo "  FAIL $name — {$e->getMessage()}\n";
    }
}

function assert_true(bool $cond, string $msg): void
{
    if (!$cond) throw new RuntimeException($msg);
}

function assert_throws(callable $fn, string $expectedFragment): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        if (str_contains($e->getMessage(), $expectedFragment)) return;
        throw new RuntimeException("mensagem inesperada: {$e->getMessage()}");
    }
    throw new RuntimeException("esperava exceção contendo '$expectedFragment'");
}

function stock_of(PDO $pdo, string $barcode): int
{
    $s = $pdo->prepare('SELECT stock FROM products WHERE barcode = :b');
    $s->execute([':b' => $barcode]);
    return (int) $s->fetchColumn();
}

function reserved_of(PDO $pdo, string $barcode): int
{
    $s = $pdo->prepare('SELECT reserved_stock FROM products WHERE barcode = :b');
    $s->execute([':b' => $barcode]);
    return (int) $s->fetchColumn();
}

echo "AutoCaixa Local — testes da Fase 1\n";

test('adiciona produto ativo pelo código de barras', function (PDO $pdo) {
    $cart = new CartService(new ProductRepository($pdo));
    $cart->addByBarcode('100');
    assert_true(array_sum($cart->items()) === 1, 'item não adicionado');
});

test('rejeita produto inativo e inexistente', function (PDO $pdo) {
    $cart = new CartService(new ProductRepository($pdo));
    assert_throws(fn() => $cart->addByBarcode('300'), 'não cadastrado');
    assert_throws(fn() => $cart->addByBarcode('999999'), 'não cadastrado');
});

test('rejeita código de barras com caracteres inválidos', function (PDO $pdo) {
    $cart = new CartService(new ProductRepository($pdo));
    assert_throws(fn() => $cart->addByBarcode("10'; DROP TABLE products;--"), 'inválido');
});

test('limita quantidade pelo estoque disponível', function (PDO $pdo) {
    $cart = new CartService(new ProductRepository($pdo));
    assert_throws(fn() => $cart->setQuantity(
        (int) $pdo->query("SELECT id FROM products WHERE barcode='200'")->fetchColumn(), 6
    ), 'Estoque insuficiente');
});

test('total é recalculado a partir do banco, não do cliente', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100', 2);
    $before = $cart->summary()['total_cents'];
    assert_true($before === 3780, "total inicial esperado 3780, veio $before");
    $pdo->exec("UPDATE products SET price_cents = 2000 WHERE barcode = '100'");
    $after = $cart->summary()['total_cents'];
    assert_true($after === 4000, "preço do banco deve prevalecer, veio $after");
});

test('remove do carrinho item cujo produto saiu de venda', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo, []);
    $cart->addByBarcode('200');
    $pdo->exec("UPDATE products SET active = 0 WHERE barcode = '200'");
    $summary = $cart->summary();
    assert_true($summary['items'] === [], 'item inativo deveria sair do resumo');
    assert_true(count($summary['removed']) === 1, 'remoção deveria ser sinalizada');
});

test('venda pendente reserva estoque sem baixar estoque', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100', 2);
    $gateway = new SimulatedPaymentGateway($pdo);
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    $result = $checkout->start($cart->items(), 'pix');
    assert_true($result['total_cents'] === 3780, 'total da venda incorreto');
    assert_true(stock_of($pdo, '100') === 10, 'estoque não deveria mudar antes do pagamento');
    assert_true(reserved_of($pdo, '100') === 2, 'estoque deveria ficar reservado durante o pagamento');
    assert_true($checkout->refresh($result['sale_id']) === 'awaiting_payment', 'deveria seguir aguardando');
});

test('venda aprovada baixa estoque uma única vez e consome a reserva', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100', 2);
    $gateway = new SimulatedPaymentGateway($pdo);
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    $result = $checkout->start($cart->items(), 'card');
    $gateway->simulateApproval($result['external_id']);

    assert_true($checkout->refresh($result['sale_id']) === 'paid', 'venda deveria ficar paga');
    assert_true($checkout->refresh($result['sale_id']) === 'paid', 'segunda consulta deveria manter paga');
    assert_true($checkout->refresh($result['sale_id']) === 'paid', 'terceira consulta deveria manter paga');
    assert_true(stock_of($pdo, '100') === 8, 'estoque deveria baixar exatamente 2 unidades, veio ' . stock_of($pdo, '100'));
    assert_true(reserved_of($pdo, '100') === 0, 'reserva deveria ser consumida após o pagamento');

    $moves = (int) $pdo->query("SELECT COUNT(*) FROM stock_movements WHERE sale_id = {$result['sale_id']}")->fetchColumn();
    assert_true($moves === 1, "deveria haver 1 movimento de estoque, houve $moves");
});

test('não finaliza sem confirmação do provedor', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('200');
    $gateway = new SimulatedPaymentGateway($pdo);
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    $result = $checkout->start($cart->items(), 'pix');
    assert_true($checkout->refresh($result['sale_id']) === 'awaiting_payment', 'não pode finalizar sem aprovação');
    assert_true(stock_of($pdo, '200') === 5, 'estoque não pode baixar sem pagamento');
    assert_true(reserved_of($pdo, '200') === 1, 'estoque deve permanecer reservado enquanto aguarda pagamento');
});

test('recusa iniciar venda com carrinho vazio', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $checkout = new CheckoutService($pdo, $repo, new SimulatedPaymentGateway($pdo));
    assert_throws(fn() => $checkout->start([], 'pix'), 'carrinho está vazio');
});

test('recusa forma de pagamento inválida', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100');
    $checkout = new CheckoutService($pdo, $repo, new SimulatedPaymentGateway($pdo));
    assert_throws(fn() => $checkout->start($cart->items(), 'cheque'), 'inválida');
});

test('recusa venda quando o estoque acabou antes do checkout', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('200', 5);
    $pdo->exec("UPDATE products SET stock = 1 WHERE barcode = '200'");
    $checkout = new CheckoutService($pdo, $repo, new SimulatedPaymentGateway($pdo));
    assert_throws(fn() => $checkout->start($cart->items(), 'pix'), 'Estoque insuficiente');
});

test('venda recusada pelo provedor vira falha e libera a reserva', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100');
    $gateway = new SimulatedPaymentGateway($pdo);
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    $result = $checkout->start($cart->items(), 'card');
    $pdo->prepare("UPDATE simulated_charges SET status = 'declined' WHERE external_id = :id")
        ->execute([':id' => $result['external_id']]);
    assert_true($checkout->refresh($result['sale_id']) === 'failed', 'venda recusada deveria ficar com falha');
    assert_true(stock_of($pdo, '100') === 10, 'estoque não pode baixar em venda recusada');
    assert_true(reserved_of($pdo, '100') === 0, 'reserva deveria ser liberada após recusa');
});

test('resultado desconhecido do gateway não marca venda como paga nem libera reserva', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100');
    $gateway = new class implements PaymentGateway {
        public function name(): string { return 'test-unknown'; }
        public function createCharge(int $amountCents, string $method, string $reference): string { throw new RuntimeException('timeout'); }
        public function status(string $externalId): string { return 'unknown'; }
    };
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    assert_throws(fn() => $checkout->start($cart->items(), 'pix'), 'resultado da cobrança não pôde ser confirmado');
    $saleId = (int) $pdo->query('SELECT id FROM sales ORDER BY id DESC LIMIT 1')->fetchColumn();
    assert_true($saleId > 0, 'venda deveria existir para reconciliação');
    assert_true($pdo->query("SELECT status FROM sales WHERE id = $saleId")->fetchColumn() === 'awaiting_payment', 'venda não pode virar falha por timeout ambíguo');
    assert_true($pdo->query("SELECT status FROM payments WHERE sale_id = $saleId")->fetchColumn() === 'unknown', 'pagamento deveria ficar desconhecido');
    assert_true(reserved_of($pdo, '100') === 1, 'reserva deve permanecer até confirmação ou expiração');
});

test('venda pendente expira e libera a reserva após o prazo', function (PDO $pdo) {
    $repo = new ProductRepository($pdo);
    $cart = new CartService($repo);
    $cart->addByBarcode('100');
    $gateway = new SimulatedPaymentGateway($pdo);
    $checkout = new CheckoutService($pdo, $repo, $gateway);
    $result = $checkout->start($cart->items(), 'pix');
    $pdo->exec("UPDATE sales SET created_at = DATE_SUB(NOW(), INTERVAL 121 SECOND) WHERE id = {$result['sale_id']}");

    assert_true($checkout->refresh($result['sale_id']) === 'expired', 'venda deveria expirar');
    assert_true(reserved_of($pdo, '100') === 0, 'reserva deveria ser liberada após expiração');
    assert_true(stock_of($pdo, '100') === 10, 'estoque físico não deve mudar na expiração');
});

echo "\n" . count($failed) . " falha(s), $passed aprovado(s)\n";
foreach ($failed as $f) echo " - $f\n";
exit($failed === [] ? 0 : 1);
