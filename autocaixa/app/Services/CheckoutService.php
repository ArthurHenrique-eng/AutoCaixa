<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\Payments\PaymentGateway;
use App\Repositories\ProductRepository;
use DomainException;
use PDO;
use RuntimeException;

/**
 * Finalização da venda. Regras críticas:
 * - o total é recalculado no servidor a partir do banco;
 * - a venda só vira "paid" após confirmação do provedor;
 * - a baixa de estoque ocorre uma única vez, mesmo com consultas ou notificações repetidas.
 */
final class CheckoutService
{
    private const METHODS = ['pix', 'card'];

    public function __construct(
        private PDO $db,
        private ProductRepository $products,
        private PaymentGateway $gateway,
    ) {}

    /** @return array{sale_id:int, payment_id:int, external_id:string, total_cents:int} */
    public function start(array $cartItems, string $method): array
    {
        if (!in_array($method, self::METHODS, true)) {
            throw new DomainException('Forma de pagamento inválida.');
        }
        if ($cartItems === []) {
            throw new DomainException('O carrinho está vazio.');
        }

        $ids = array_map('intval', array_keys($cartItems));
        $this->db->beginTransaction();
        try {
            $products = $this->products->lockByIds($ids);
            $total = 0;
            $lines = [];
            foreach ($cartItems as $productId => $qty) {
                $p = $products[(int) $productId] ?? null;
                if ($p === null || (int) $p['active'] !== 1) {
                    throw new DomainException('Um produto saiu de venda. Revise o carrinho.');
                }
                if ((int) $p['stock'] < (int) $qty) {
                    throw new DomainException('Estoque insuficiente para ' . $p['name'] . '.');
                }
                $subtotal = (int) $p['price_cents'] * (int) $qty;
                $total += $subtotal;
                $lines[] = [$p, (int) $qty, $subtotal];
            }

            $this->db->prepare(
                "INSERT INTO sales (status, total_cents) VALUES ('awaiting_payment', :total)"
            )->execute([':total' => $total]);
            $saleId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO sale_items (sale_id, product_id, product_name, unit_price_cents, quantity, subtotal_cents)
                 VALUES (:sale, :product, :name, :unit, :qty, :subtotal)'
            );
            foreach ($lines as [$p, $qty, $subtotal]) {
                $itemStmt->execute([
                    ':sale' => $saleId, ':product' => (int) $p['id'], ':name' => $p['name'],
                    ':unit' => (int) $p['price_cents'], ':qty' => $qty, ':subtotal' => $subtotal,
                ]);
            }

            $this->db->prepare(
                "INSERT INTO payments (sale_id, method, status, amount_cents, provider)
                 VALUES (:sale, :method, 'pending', :amount, :provider)"
            )->execute([
                ':sale' => $saleId, ':method' => $method, ':amount' => $total, ':provider' => $this->gateway->name(),
            ]);
            $paymentId = (int) $this->db->lastInsertId();
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        try {
            $externalId = $this->gateway->createCharge($total, $method, 'SALE-' . $saleId);
        } catch (\Throwable $e) {
            $this->db->prepare("UPDATE payments SET status = 'failed' WHERE id = :id")->execute([':id' => $paymentId]);
            $this->db->prepare("UPDATE sales SET status = 'failed' WHERE id = :id")->execute([':id' => $saleId]);
            throw new RuntimeException('Não foi possível criar a cobrança. Tente novamente ou chame um funcionário.', 0, $e);
        }

        $this->db->prepare('UPDATE payments SET external_id = :ext WHERE id = :id')
            ->execute([':ext' => $externalId, ':id' => $paymentId]);

        return ['sale_id' => $saleId, 'payment_id' => $paymentId, 'external_id' => $externalId, 'total_cents' => $total];
    }

    /**
     * Consulta o estado no provedor e finaliza a venda somente se houver aprovação confirmada.
     * Pode ser chamado repetidamente sem efeito colateral extra.
     */
    public function refresh(int $saleId): string
    {
        $sale = $this->fetchSale($saleId);
        if ($sale === null) {
            throw new DomainException('Venda não encontrada.');
        }
        if ($sale['status'] !== 'awaiting_payment') {
            return $sale['status'];
        }

        $payment = $this->latestPendingPayment($saleId);
        if ($payment === null || $payment['external_id'] === null) {
            return 'awaiting_payment';
        }

        $remote = $this->gateway->status($payment['external_id']);
        if ($remote === 'approved') {
            $this->finalizePaid($saleId, (int) $payment['id']);
            return 'paid';
        }
        if ($remote === 'declined') {
            $this->db->prepare("UPDATE payments SET status = 'declined' WHERE id = :id AND status = 'pending'")
                ->execute([':id' => $payment['id']]);
            $this->db->prepare("UPDATE sales SET status = 'failed' WHERE id = :id AND status = 'awaiting_payment'")
                ->execute([':id' => $saleId]);
            return 'failed';
        }
        return 'awaiting_payment';
    }

    /** Baixa de estoque e mudança de estado em uma única transação, sem duplicar nada. */
    private function finalizePaid(int $saleId, int $paymentId): void
    {
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                "UPDATE sales SET status = 'paid' WHERE id = :id AND status = 'awaiting_payment'"
            );
            $upd->execute([':id' => $saleId]);
            if ($upd->rowCount() === 0) {
                // Outra requisição já finalizou esta venda. Não baixar estoque de novo.
                $this->db->commit();
                return;
            }

            $this->db->prepare("UPDATE payments SET status = 'approved' WHERE id = :id AND status = 'pending'")
                ->execute([':id' => $paymentId]);

            $itemsStmt = $this->db->prepare('SELECT product_id, quantity FROM sale_items WHERE sale_id = :sale');
            $itemsStmt->execute([':sale' => $saleId]);
            $items = $itemsStmt->fetchAll();

            $decrement = $this->db->prepare(
                'UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty_check'
            );
            $movement = $this->db->prepare(
                "INSERT INTO stock_movements (product_id, quantity_change, reason, sale_id)
                 VALUES (:id, :change, 'sale', :sale)"
            );
            foreach ($items as $item) {
                $decrement->execute([':qty' => (int) $item['quantity'], ':qty_check' => (int) $item['quantity'], ':id' => (int) $item['product_id']]);
                if ($decrement->rowCount() === 0) {
                    // Estoque insuficiente após o pagamento. Reverter tudo e sinalizar para atendimento.
                    throw new RuntimeException('Estoque insuficiente na finalização. Chame um funcionário.');
                }
                $movement->execute([
                    ':id' => (int) $item['product_id'], ':change' => -(int) $item['quantity'], ':sale' => $saleId,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function externalIdFor(int $saleId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT external_id FROM payments WHERE sale_id = :sale AND external_id IS NOT NULL ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([":sale" => $saleId]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (string) $value;
    }

    private function fetchSale(int $saleId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status, total_cents FROM sales WHERE id = :id');
        $stmt->execute([':id' => $saleId]);
        return $stmt->fetch() ?: null;
    }

    private function latestPendingPayment(int $saleId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, external_id, status FROM payments WHERE sale_id = :sale AND status = 'pending'
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':sale' => $saleId]);
        return $stmt->fetch() ?: null;
    }
}
