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
 * - o estoque é reservado durante o pagamento para impedir overselling;
 * - a venda só vira "paid" após confirmação do provedor;
 * - a baixa definitiva e a liberação da reserva são transacionais e idempotentes;
 * - falhas com resultado de pagamento desconhecido não são tratadas como pagamento recusado.
 */
final class CheckoutService
{
    private const METHODS = ['pix', 'card'];
    private const RESERVATION_TTL_SECONDS = 120;

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
            $this->expireStaleSales();
            $products = $this->products->lockByIds($ids);
            $total = 0;
            $lines = [];
            foreach ($cartItems as $productId => $qty) {
                $qty = (int) $qty;
                $p = $products[(int) $productId] ?? null;
                if ($p === null || (int) $p['active'] !== 1) {
                    throw new DomainException('Um produto saiu de venda. Revise o carrinho.');
                }
                $available = (int) $p['stock'] - (int) $p['reserved_stock'];
                if ($available < $qty) {
                    throw new DomainException('Estoque insuficiente para ' . $p['name'] . '.');
                }
                $subtotal = (int) $p['price_cents'] * $qty;
                $total += $subtotal;
                $lines[] = [$p, $qty, $subtotal];
            }

            $this->db->prepare(
                "INSERT INTO sales (status, total_cents) VALUES ('awaiting_payment', :total)"
            )->execute([':total' => $total]);
            $saleId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO sale_items (sale_id, product_id, product_name, unit_price_cents, quantity, subtotal_cents)
                 VALUES (:sale, :product, :name, :unit, :qty, :subtotal)'
            );
            $reserveStmt = $this->db->prepare(
                'UPDATE products
                 SET reserved_stock = reserved_stock + :qty
                 WHERE id = :id AND stock - reserved_stock >= :available_qty'
            );
            foreach ($lines as [$p, $qty, $subtotal]) {
                $itemStmt->execute([
                    ':sale' => $saleId, ':product' => (int) $p['id'], ':name' => $p['name'],
                    ':unit' => (int) $p['price_cents'], ':qty' => $qty, ':subtotal' => $subtotal,
                ]);
                $reserveStmt->execute([
                    ':qty' => $qty,
                    ':id' => (int) $p['id'],
                    ':available_qty' => $qty,
                ]);
                if ($reserveStmt->rowCount() !== 1) {
                    throw new RuntimeException('Não foi possível reservar o estoque. Tente novamente.');
                }
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
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        try {
            $externalId = $this->gateway->createCharge($total, $method, 'SALE-' . $saleId);
        } catch (\Throwable $e) {
            // O provedor pode ter recebido a cobrança antes de a conexão falhar.
            // Não liberamos a reserva nem marcamos a venda como recusada sem confirmação.
            $this->db->prepare("UPDATE payments SET status = 'unknown' WHERE id = :id AND status = 'pending'")
                ->execute([':id' => $paymentId]);
            throw new RuntimeException(
                'Não foi possível confirmar o resultado da cobrança. Não tente pagar novamente; chame um funcionário.',
                0,
                $e
            );
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

        if (strtotime((string) $sale['created_at']) <= time() - self::RESERVATION_TTL_SECONDS) {
            $this->expireSale($saleId);
            return 'expired';
        }

        $payment = $this->latestPayment($saleId);
        if ($payment === null || $payment['external_id'] === null) {
            return 'awaiting_payment';
        }

        try {
            $remote = $this->gateway->status($payment['external_id']);
        } catch (\Throwable $e) {
            $this->markPaymentUnknown((int) $payment['id']);
            return 'awaiting_payment';
        }

        if ($remote === 'approved') {
            $this->finalizePaid($saleId, (int) $payment['id']);
            return 'paid';
        }
        if ($remote === 'declined') {
            $this->failAndRelease($saleId, (int) $payment['id'], 'declined');
            return 'failed';
        }
        if ($remote === 'unknown') {
            $this->markPaymentUnknown((int) $payment['id']);
        }
        return 'awaiting_payment';
    }

    /** Baixa definitiva e mudança de estado em uma única transação, sem duplicar nada. */
    private function finalizePaid(int $saleId, int $paymentId): void
    {
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                "UPDATE sales SET status = 'paid' WHERE id = :id AND status = 'awaiting_payment'"
            );
            $upd->execute([':id' => $saleId]);
            if ($upd->rowCount() === 0) {
                $this->db->commit();
                return;
            }

            $this->db->prepare("UPDATE payments SET status = 'approved' WHERE id = :id AND status IN ('pending','unknown')")
                ->execute([':id' => $paymentId]);

            $itemsStmt = $this->db->prepare('SELECT product_id, quantity FROM sale_items WHERE sale_id = :sale');
            $itemsStmt->execute([':sale' => $saleId]);
            $items = $itemsStmt->fetchAll();

            $decrement = $this->db->prepare(
                'UPDATE products
                 SET stock = stock - :qty, reserved_stock = reserved_stock - :qty
                 WHERE id = :id AND stock >= :qty_check AND reserved_stock >= :qty_check'
            );
            $movement = $this->db->prepare(
                "INSERT INTO stock_movements (product_id, quantity_change, reason, sale_id)
                 VALUES (:id, :change, 'sale', :sale)"
            );
            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $decrement->execute([
                    ':qty' => $qty,
                    ':qty_check' => $qty,
                    ':id' => (int) $item['product_id'],
                ]);
                if ($decrement->rowCount() !== 1) {
                    throw new RuntimeException('Estoque inconsistente na finalização. Chame um funcionário.');
                }
                $movement->execute([
                    ':id' => (int) $item['product_id'], ':change' => -$qty, ':sale' => $saleId,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function externalIdFor(int $saleId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT external_id FROM payments WHERE sale_id = :sale AND external_id IS NOT NULL ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':sale' => $saleId]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (string) $value;
    }

    private function fetchSale(int $saleId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status, total_cents, created_at FROM sales WHERE id = :id');
        $stmt->execute([':id' => $saleId]);
        return $stmt->fetch() ?: null;
    }

    private function latestPayment(int $saleId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, external_id, status FROM payments WHERE sale_id = :sale ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':sale' => $saleId]);
        return $stmt->fetch() ?: null;
    }

    private function markPaymentUnknown(int $paymentId): void
    {
        $this->db->prepare("UPDATE payments SET status = 'unknown' WHERE id = :id AND status IN ('pending','unknown')")
            ->execute([':id' => $paymentId]);
    }

    private function failAndRelease(int $saleId, int $paymentId, string $paymentStatus): void
    {
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                "UPDATE sales SET status = 'failed' WHERE id = :id AND status = 'awaiting_payment'"
            );
            $upd->execute([':id' => $saleId]);
            if ($upd->rowCount() === 1) {
                $this->db->prepare("UPDATE payments SET status = :status WHERE id = :id AND status IN ('pending','unknown')")
                    ->execute([':status' => $paymentStatus, ':id' => $paymentId]);
                $this->releaseReservation($saleId);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function expireSale(int $saleId): void
    {
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                "UPDATE sales SET status = 'expired' WHERE id = :id AND status = 'awaiting_payment'"
            );
            $upd->execute([':id' => $saleId]);
            if ($upd->rowCount() === 1) {
                $this->db->prepare("UPDATE payments SET status = 'expired' WHERE sale_id = :id AND status IN ('pending','unknown')")
                    ->execute([':id' => $saleId]);
                $this->releaseReservation($saleId);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Libera reservas vencidas antes de validar um novo checkout. */
    private function expireStaleSales(): void
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM sales
             WHERE status = 'awaiting_payment'
               AND created_at < DATE_SUB(NOW(), INTERVAL :ttl SECOND)
             FOR UPDATE"
        );
        $stmt->bindValue(':ttl', self::RESERVATION_TTL_SECONDS, PDO::PARAM_INT);
        $stmt->execute();
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        foreach ($ids as $saleId) {
            $this->db->prepare("UPDATE payments SET status = 'expired' WHERE sale_id = :id AND status IN ('pending','unknown')")
                ->execute([':id' => $saleId]);
            $this->releaseReservation($saleId);
            $this->db->prepare("UPDATE sales SET status = 'expired' WHERE id = :id AND status = 'awaiting_payment'")
                ->execute([':id' => $saleId]);
        }
    }

    private function releaseReservation(int $saleId): void
    {
        $itemsStmt = $this->db->prepare('SELECT product_id, quantity FROM sale_items WHERE sale_id = :sale');
        $itemsStmt->execute([':sale' => $saleId]);
        $items = $itemsStmt->fetchAll();

        $release = $this->db->prepare(
            'UPDATE products
             SET reserved_stock = reserved_stock - :qty
             WHERE id = :id AND reserved_stock >= :qty_check'
        );
        foreach ($items as $item) {
            $qty = (int) $item['quantity'];
            $release->execute([
                ':qty' => $qty,
                ':qty_check' => $qty,
                ':id' => (int) $item['product_id'],
            ]);
            if ($release->rowCount() !== 1) {
                throw new RuntimeException('Não foi possível liberar uma reserva de estoque. Chame um funcionário.');
            }
        }
    }
}
