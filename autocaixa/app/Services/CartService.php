<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProductRepository;
use DomainException;

/**
 * Regras do carrinho. O estado é uma lista [product_id => quantidade] guardada na sessão,
 * e preços, nomes e disponibilidade são sempre recalculados a partir do banco.
 */
final class CartService
{
    public const MAX_QUANTITY_PER_ITEM = 99;

    /** @param array<int,int> $items */
    public function __construct(private ProductRepository $products, private array $items = []) {}

    /** @return array<int,int> */
    public function items(): array
    {
        return $this->items;
    }

    public function addByBarcode(string $barcode, int $quantity = 1): void
    {
        $barcode = trim($barcode);
        if ($barcode === '' || !ctype_digit($barcode) || strlen($barcode) > 64) {
            throw new DomainException('Código de barras inválido.');
        }
        $product = $this->products->findActiveByBarcode($barcode);
        if ($product === null) {
            throw new DomainException('Produto não cadastrado ou indisponível.');
        }
        $id = (int) $product['id'];
        $this->setQuantity($id, ($this->items[$id] ?? 0) + $quantity);
    }

    public function setQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        }
        if ($quantity > self::MAX_QUANTITY_PER_ITEM) {
            throw new DomainException('Quantidade máxima por item é ' . self::MAX_QUANTITY_PER_ITEM . '.');
        }
        $product = $this->products->findActiveByIds([$productId])[$productId] ?? null;
        if ($product === null) {
            $this->remove($productId);
            throw new DomainException('Produto indisponível e removido do carrinho.');
        }
        if ($quantity > (int) $product['stock']) {
            throw new DomainException('Estoque insuficiente para ' . $product['name'] . '.');
        }
        $this->items[$productId] = $quantity;
    }

    public function remove(int $productId): void
    {
        unset($this->items[$productId]);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Resumo calculado no servidor. Itens cujo produto ficou inativo são removidos e sinalizados.
     *
     * @return array{items: list<array>, total_cents: int, removed: list<string>}
     */
    public function summary(): array
    {
        $ids = array_keys($this->items);
        $products = $this->products->findActiveByIds($ids);
        $lines = [];
        $total = 0;
        $removed = [];
        foreach ($this->items as $id => $qty) {
            if (!isset($products[$id])) {
                unset($this->items[$id]);
                $removed[] = 'Um produto saiu de venda e foi removido do carrinho.';
                continue;
            }
            $p = $products[$id];
            $subtotal = (int) $p['price_cents'] * $qty;
            $total += $subtotal;
            $lines[] = [
                'product_id'       => (int) $id,
                'name'             => $p['name'],
                'barcode'          => $p['barcode'],
                'unit_price_cents' => (int) $p['price_cents'],
                'quantity'         => $qty,
                'subtotal_cents'   => $subtotal,
            ];
        }
        return ['items' => $lines, 'total_cents' => $total, 'removed' => $removed];
    }
}
