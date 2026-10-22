<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\CartService;
use PDO;

/** Páginas públicas do totem. Renderizadas no servidor, com dados vindos do backend. */
final class PageController
{
    public function __construct(private CartService $cart, private PDO $db) {}

    public function home(): void
    {
        View::render('home');
    }

    public function cart(): void
    {
        View::render('cart', ['summary' => $this->cart->summary()]);
    }

    public function payment(int $saleId): void
    {
        if (!in_array($saleId, $_SESSION['sales'] ?? [], true)) {
            View::render('error', ['message' => 'Venda não encontrada.'], 404);
            return;
        }
        View::render('payment', ['saleId' => $saleId, 'sale' => $this->fetchSale($saleId)]);
    }

    public function receipt(int $saleId): void
    {
        if (!in_array($saleId, $_SESSION['sales'] ?? [], true)) {
            View::render('error', ['message' => 'Comprovante indisponível.'], 404);
            return;
        }
        $sale = $this->fetchSale($saleId);
        if ($sale === null || $sale['status'] !== 'paid') {
            View::render('error', ['message' => 'Comprovante disponível somente para vendas pagas.'], 409);
            return;
        }
        $stmt = $this->db->prepare(
            'SELECT product_name, unit_price_cents, quantity, subtotal_cents FROM sale_items WHERE sale_id = :id'
        );
        $stmt->execute([':id' => $saleId]);
        View::render('receipt', ['sale' => $sale, 'items' => $stmt->fetchAll()]);
    }

    private function fetchSale(int $saleId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status, total_cents FROM sales WHERE id = :id');
        $stmt->execute([':id' => $saleId]);
        return $stmt->fetch() ?: null;
    }
}
