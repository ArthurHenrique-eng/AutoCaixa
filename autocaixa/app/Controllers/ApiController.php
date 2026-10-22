<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Json;
use App\Integrations\Payments\SimulatedPaymentGateway;
use App\Services\CartService;
use App\Services\CheckoutService;
use DomainException;
use RuntimeException;

/** Endpoints JSON usados pelo totem. Toda operação de preço, pagamento e estoque acontece aqui, no servidor. */
final class ApiController
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
        private array $config,
        private ?object $gateway = null,
    ) {}

    public function cartGet(): void
    {
        Json::send($this->cart->summary());
    }

    public function cartAdd(): void
    {
        $this->guardPost();
        $body = $this->body();
        $this->run(function () use ($body) {
            $this->cart->addByBarcode((string) ($body['barcode'] ?? ''));
            $this->persistCart();
        });
    }

    public function cartSetQuantity(): void
    {
        $this->guardPost();
        $body = $this->body();
        $this->run(function () use ($body) {
            $this->cart->setQuantity((int) ($body['product_id'] ?? 0), (int) ($body['quantity'] ?? 0));
            $this->persistCart();
        });
    }

    public function cartRemove(): void
    {
        $this->guardPost();
        $body = $this->body();
        $this->run(function () use ($body) {
            $this->cart->remove((int) ($body['product_id'] ?? 0));
            $this->persistCart();
        });
    }

    public function checkoutStart(): void
    {
        $this->guardPost();
        $body = $this->body();
        $this->run(function () use ($body) {
            $result = $this->checkout->start($this->cart->items(), (string) ($body['method'] ?? ''));
            $_SESSION['sales'][] = $result['sale_id'];
            Json::send([
                'sale_id'     => $result['sale_id'],
                'total_cents' => $result['total_cents'],
                'redirect'    => '/pagamento/' . $result['sale_id'],
            ], 201);
        });
    }

    public function saleStatus(int $saleId): void
    {
        if (!$this->owns($saleId)) {
            Json::send(['error' => 'Venda não encontrada.'], 404);
            return;
        }
        $this->run(function () use ($saleId) {
            $status = $this->checkout->refresh($saleId);
            if ($status === 'paid') {
                $this->cart->clear();
                $this->persistCart();
            }
            Json::send(['sale_id' => $saleId, 'status' => $status]);
        });
    }

    /** Aprovação simulada. Disponível apenas com o driver de simulação ativo. */
    public function saleSimulateApproval(int $saleId): void
    {
        $this->guardPost();
        if (!$this->owns($saleId)) {
            Json::send(['error' => 'Venda não encontrada.'], 404);
            return;
        }
        if (!$this->gateway instanceof SimulatedPaymentGateway) {
            Json::send(['error' => 'Recurso indisponível.'], 404);
            return;
        }
        $this->run(function () use ($saleId) {
            $externalId = $this->checkout->externalIdFor($saleId);
            if ($externalId !== null) {
                $this->gateway->simulateApproval($externalId);
            }
            Json::send(['sale_id' => $saleId, 'simulated' => true]);
        });
    }

    private function run(callable $action): void
    {
        try {
            $action();
        } catch (DomainException $e) {
            Json::send(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            error_log('[AutoCaixa] ' . $e->getMessage());
            Json::send(['error' => $e->getMessage()], 503);
        } catch (\Throwable $e) {
            error_log('[AutoCaixa] ' . $e->getMessage());
            Json::send(['error' => 'Erro interno. Chame um funcionário.'], 500);
        }
    }

    private function guardPost(): void
    {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::validate($header)) {
            Json::send(['error' => 'Requisição inválida. Recarregue a página.'], 419);
            exit;
        }
    }

    private function body(): array
    {
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $data = json_decode((string) file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }
        return $_POST;
    }

    private function owns(int $saleId): bool
    {
        return in_array($saleId, $_SESSION['sales'] ?? [], true);
    }

    private function persistCart(): void
    {
        $_SESSION['cart'] = $this->cart->items();
    }
}
