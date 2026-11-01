<?php
declare(strict_types=1);

use App\Controllers\AdminCatalogController;
use App\Controllers\AdminController;
use App\Controllers\AdminSalesController;
use App\Controllers\ApiController;
use App\Controllers\PageController;
use App\Core\Database;
use App\Core\Ip;
use App\Core\Json;
use App\Core\ProductionGuard;
use App\Core\RateLimiter;
use App\Core\TooManyRequestsException;
use App\Core\View;
use App\Integrations\Payments\SimulatedPaymentGateway;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\CheckoutService;

// Diretório público contém apenas este ponto de entrada e os assets. Código e configurações ficam fora.
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$config = require dirname(__DIR__) . '/config/config.php';
$GLOBALS['appConfig'] = $config;

// Sessão endurecida: não aceita um ID de sessão inventado pelo cliente (strict_mode) e só
// aceita o ID vindo de cookie, nunca de URL (use_only_cookies).
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'secure'   => ($_SERVER['HTTPS'] ?? '') === 'on',
    'samesite' => 'Lax',
]);
session_start();
$_SESSION['cart'] ??= [];
$_SESSION['sales'] ??= [];

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// Totens são compartilhados: o carrinho e as vendas transitórias não podem sobreviver
// indefinidamente à inatividade do operador. A expiração também é aplicada no servidor,
// independentemente do temporizador JavaScript do navegador.
$isTotemRequest = !str_starts_with($path, '/admin') && $path !== '/health';
if ($isTotemRequest) {
    $now = time();
    $lastActivity = (int) ($_SESSION['totem_last_activity'] ?? 0);
    if ($lastActivity > 0 && ($now - $lastActivity) >= 120) {
        $_SESSION['cart'] = [];
        $_SESSION['sales'] = [];
    }
    $_SESSION['totem_last_activity'] = $now;
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'");
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (($_SERVER["HTTPS"] ?? "") === "on") {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
// Páginas que podem mostrar dados de sessão, pagamento ou conta não devem ficar em cache.
// A API já envia no-store em toda resposta (Json::send); aqui cobrimos o restante em HTML.
$noStorePaths = ['/admin', '/pagamento', '/comprovante', '/comprar'];
foreach ($noStorePaths as $prefix) {
    if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
        header('Cache-Control: no-store');
        break;
    }
}

// Bloqueio de produção: verificado ANTES de qualquer conexão ou rota, porque um monitoramento
// não pode reportar "ok" para uma aplicação que não tem permissão de rodar nesta configuração.
// Fora do try principal para que o erro seja sempre logado, independente do banco.
try {
    ProductionGuard::assertPaymentDriverAllowed($config['app']['env'], $config['payment']['driver']);
} catch (\RuntimeException $e) {
    error_log('[AutoCaixa] ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['error' => ['code' => 'service_unavailable', 'message' => 'Serviço indisponível no momento.']], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Database::connect($config['db']);

    if ($path === '/health' && $method === 'GET') {
        $pdo->query('SELECT 1');
        Json::send(['status' => 'ok']);
        exit;
    }

    // Limitação de requisições por IP (e por sessão, nas rotas do totem), com limites e
    // janelas vindos da configuração. A verificação acontece antes de qualquer outra lógica,
    // para que um excesso de requisições nunca chegue a tocar regras de negócio ou o CSRF.
    $clientIp = Ip::client($config['trusted_proxies']);
    $limits = $config['rate_limits'];
    RateLimiter::check($pdo, 'public:' . $clientIp, $limits['public']['limit'], $limits['public']['window']);
    if ($path === '/admin/login' && $method === 'POST') {
        RateLimiter::check($pdo, 'login:' . $clientIp, $limits['login']['limit'], $limits['login']['window']);
    }
    if (str_starts_with($path, '/api/carrinho')) {
        RateLimiter::check(
            $pdo, 'cart:' . $clientIp . ':' . session_id(), $limits['cart']['limit'], $limits['cart']['window']
        );
    }
    if ($path === '/api/checkout' && $method === 'POST') {
        RateLimiter::check(
            $pdo, 'checkout:' . $clientIp . ':' . session_id(), $limits['checkout']['limit'], $limits['checkout']['window']
        );
    }

    $products = new ProductRepository($pdo);
    $gateway = match ($config['payment']['driver']) {
        'simulated' => new SimulatedPaymentGateway($pdo),
        default     => throw new RuntimeException('Driver de pagamento não configurado. A integração real está pendente.'),
    };

    $cart = new CartService($products, array_map('intval', $_SESSION['cart']));
    $checkout = new CheckoutService($pdo, $products, $gateway);

    $pages = new PageController($cart, $pdo);
    $api = new ApiController($cart, $checkout, $config, $gateway);
    $admin = new AdminController($pdo);
    $catalog = new AdminCatalogController($pdo, $admin);
    $sales = new AdminSalesController($pdo, $admin);

    $routes = [
        // Totem (público)
        ['GET',  '#^/$#',                           fn() => $pages->home()],
        ['GET',  '#^/comprar$#',                    fn() => $pages->cart()],
        ['GET',  '#^/pagamento/(\d+)$#',            fn($id) => $pages->payment((int) $id)],
        ['GET',  '#^/comprovante/(\d+)$#',          fn($id) => $pages->receipt((int) $id)],
        ['GET',  '#^/api/carrinho$#',               fn() => $api->cartGet()],
        ['POST', '#^/api/carrinho/adicionar$#',     fn() => $api->cartAdd()],
        ['POST', '#^/api/carrinho/quantidade$#',    fn() => $api->cartSetQuantity()],
        ['POST', '#^/api/carrinho/remover$#',       fn() => $api->cartRemove()],
        ['POST', '#^/api/checkout$#',               fn() => $api->checkoutStart()],
        ['GET',  '#^/api/vendas/(\d+)/status$#',    fn($id) => $api->saleStatus((int) $id)],

        // Painel administrativo (exige login e perfil em cada ação)
        ['GET',  '#^/admin/login$#',                fn() => $admin->loginForm()],
        ['POST', '#^/admin/login$#',                fn() => $admin->login()],
        ['POST', '#^/admin/sair$#',                 fn() => $admin->logout()],
        ['GET',  '#^/admin/trocar-senha$#',         fn() => $admin->passwordChangeForm()],
        ['POST', '#^/admin/trocar-senha$#',         fn() => $admin->passwordChangeSubmit()],
        ['GET',  '#^/admin$#',                      fn() => $admin->dashboard()],
        ['GET',  '#^/admin/vendas$#',               fn() => $sales->sales()],
        ['GET',  '#^/admin/vendas/(\d+)$#',         fn($id) => $sales->saleDetail((int) $id)],
        ['POST', '#^/admin/vendas/(\d+)/cancelar$#', fn($id) => $sales->saleCancel((int) $id)],
        ['GET',  '#^/admin/produtos$#',             fn() => $catalog->products()],
        ['POST', '#^/admin/produtos$#',             fn() => $catalog->productCreate()],
        ['POST', '#^/admin/produtos/(\d+)$#',       fn($id) => $catalog->productUpdate((int) $id)],
        ['POST', '#^/admin/produtos/(\d+)/estoque$#', fn($id) => $catalog->stockAdjust((int) $id)],
        ['GET',  '#^/admin/relatorios$#',           fn() => $sales->report()],
        ['GET',  '#^/admin/relatorios/csv$#',      fn() => $sales->reportCsv()],
        ['GET',  '#^/admin/usuarios$#',             fn() => $admin->users()],
        ['POST', '#^/admin/usuarios$#',             fn() => $admin->userCreate()],
        ['POST', '#^/admin/usuarios/(\d+)/situacao$#', fn($id) => $admin->userToggle((int) $id)],
        ['GET',  '#^/admin/auditoria$#',            fn() => $admin->audit()],
    ];

    // A rota de aprovação simulada só existe quando o gateway em uso é mesmo o simulado.
    // Com um gateway real, ela nunca é registrada — não há como acessá-la por engano.
    if ($gateway instanceof SimulatedPaymentGateway) {
        $routes[] = ['POST', '#^/api/vendas/(\d+)/simular-aprovacao$#', fn($id) => $api->saleSimulateApproval((int) $id)];
    }

    foreach ($routes as [$routeMethod, $pattern, $handler]) {
        if ($routeMethod === $method && preg_match($pattern, $path, $m)) {
            $handler(...array_slice($m, 1));
            exit;
        }
    }

    if (str_starts_with($path, '/api/')) {
        Json::error('ROUTE_NOT_FOUND', 'Rota não encontrada.', 404);
    } else {
        View::render('error', ['message' => 'Página não encontrada.'], 404);
    }
} catch (TooManyRequestsException $e) {
    header('Retry-After: ' . $e->retryAfterSeconds);
    if (str_starts_with($path, '/api/')) {
        Json::error('TOO_MANY_REQUESTS', 'Muitas requisições. Tente novamente em instantes.', 429);
    } else {
        View::render('error', ['message' => 'Muitas requisições. Tente novamente em instantes.'], 429);
    }
} catch (Throwable $e) {
    error_log('[AutoCaixa] ' . $e->getMessage());
    if (str_starts_with($path, '/api/')) {
        Json::error('SERVICE_UNAVAILABLE', 'Serviço indisponível no momento. Chame um funcionário.', 503);
    } else {
        View::render('error', ['message' => 'Serviço indisponível no momento. Chame um funcionário.'], 503);
    }
}
