<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $db) {}

    /** Produto ativo pelo código de barras. A disponibilidade considera reservas temporárias. */
    public function findActiveByBarcode(string $barcode): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, barcode, name, price_cents, (stock - reserved_stock) AS stock
             FROM products
             WHERE barcode = :barcode AND active = 1
             LIMIT 1'
        );
        $stmt->execute([':barcode' => $barcode]);
        return $stmt->fetch() ?: null;
    }

    /** Produtos ativos por ids, indexados pelo id. A disponibilidade considera reservas temporárias. */
    public function findActiveByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, barcode, name, price_cents, (stock - reserved_stock) AS stock
             FROM products
             WHERE active = 1 AND id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[(int) $row['id']] = $row;
        }
        return $rows;
    }

    /** Produtos bloqueados para uma operação transacional de estoque. */
    public function lockByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, barcode, name, price_cents, stock, reserved_stock, active
             FROM products
             WHERE id IN ($placeholders)
             FOR UPDATE"
        );
        $stmt->execute(array_values($ids));
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[(int) $row['id']] = $row;
        }
        return $rows;
    }
}
