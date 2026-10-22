<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Http;
use App\Core\View;
use PDO;

/** Cadastro de produtos e ajustes de estoque. Toda regra é validada no servidor. */
final class AdminCatalogController
{
    private const STOCK_REASONS = ['entrada', 'ajuste', 'perda'];

    public function __construct(private PDO $db, private AdminController $auth) {}

    public function products(): void
    {
        $user = $this->auth->requireUser('products.manage');
        if ($user === null) return;
        $rows = $this->db->query(
            'SELECT id, barcode, name, price_cents, stock, min_stock, active FROM products ORDER BY name'
        )->fetchAll();
        View::render('admin/products', ['user' => $user, 'rows' => $rows]);
    }

    public function productCreate(): void
    {
        $user = $this->auth->requireUser('products.manage');
        if ($user === null || !Http::formCsrfOk()) {
            if ($user !== null) Http::flash('error', 'Sessão expirada. Tente novamente.');
            if ($user !== null) Http::redirect('/admin/produtos');
            return;
        }
        $data = $this->validatedProduct();
        if (is_string($data)) {
            { $this->back('error', $data); return; }
        }
        try {
            $this->db->prepare(
                'INSERT INTO products (barcode, name, price_cents, stock, min_stock, active)
                 VALUES (:b, :n, :p, :s, :m, :a)'
            )->execute([
                ':b' => $data['barcode'], ':n' => $data['name'], ':p' => $data['price_cents'],
                ':s' => $data['stock'], ':m' => $data['min_stock'], ':a' => $data['active'],
            ]);
        } catch (\PDOException $e) {
            { $this->back('error', 'Já existe um produto com este código de barras.'); return; }
        }
        Audit::log($this->db, (int) $user['id'], 'product_created', 'product', (string) $this->db->lastInsertId(),
            ['barcode' => $data['barcode']], Auth::ip());
        $this->back('ok', 'Produto cadastrado.');
    }

    public function productUpdate(int $id): void
    {
        $user = $this->auth->requireUser('products.manage');
        if ($user === null || !Http::formCsrfOk()) {
            if ($user !== null) Http::flash('error', 'Sessão expirada. Tente novamente.');
            if ($user !== null) Http::redirect('/admin/produtos');
            return;
        }
        $current = $this->findProduct($id);
        if ($current === null) {
            { $this->back('error', 'Produto não encontrado.'); return; }
        }
        $data = $this->validatedProduct();
        if (is_string($data)) {
            { $this->back('error', $data); return; }
        }
        try {
            $this->db->prepare(
                'UPDATE products SET barcode = :b, name = :n, price_cents = :p, min_stock = :m, active = :a WHERE id = :id'
            )->execute([
                ':b' => $data['barcode'], ':n' => $data['name'], ':p' => $data['price_cents'],
                ':m' => $data['min_stock'], ':a' => $data['active'], ':id' => $id,
            ]);
        } catch (\PDOException $e) {
            { $this->back('error', 'Já existe outro produto com este código de barras.'); return; }
        }
        $changes = [];
        foreach (['barcode', 'name', 'price_cents', 'min_stock', 'active'] as $field) {
            if ((string) $current[$field] !== (string) $data[$field]) {
                $changes[$field] = ['de' => $current[$field], 'para' => $data[$field]];
            }
        }
        Audit::log($this->db, (int) $user['id'], 'product_updated', 'product', (string) $id, $changes, Auth::ip());
        $this->back('ok', 'Produto atualizado.');
    }

    public function stockAdjust(int $id): void
    {
        $user = $this->auth->requireUser('stock.adjust');
        if ($user === null || !Http::formCsrfOk()) {
            if ($user !== null) Http::flash('error', 'Sessão expirada. Tente novamente.');
            if ($user !== null) Http::redirect('/admin/produtos');
            return;
        }
        $delta = filter_var($_POST['delta'] ?? null, FILTER_VALIDATE_INT);
        $reason = Http::input('reason', 20);
        if ($delta === false || $delta === 0 || abs($delta) > 100000) {
            { $this->back('error', 'Informe uma quantidade inteira diferente de zero.'); return; }
        }
        if (!in_array($reason, self::STOCK_REASONS, true)) {
            { $this->back('error', 'Motivo inválido.'); return; }
        }
        if ($reason === 'entrada' && $delta < 0) {
            { $this->back('error', 'Entrada deve ter quantidade positiva.'); return; }
        }
        if ($reason === 'perda' && $delta > 0) {
            { $this->back('error', 'Perda deve ter quantidade negativa.'); return; }
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT stock FROM products WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $id]);
            $stock = $stmt->fetchColumn();
            if ($stock === false) {
                $this->db->rollBack();
                { $this->back('error', 'Produto não encontrado.'); return; }
            }
            if ((int) $stock + $delta < 0) {
                $this->db->rollBack();
                { $this->back('error', 'O estoque não pode ficar negativo.'); return; }
            }
            $this->db->prepare('UPDATE products SET stock = stock + :d WHERE id = :id')
                ->execute([':d' => $delta, ':id' => $id]);
            $this->db->prepare(
                'INSERT INTO stock_movements (product_id, quantity_change, reason, user_id) VALUES (:id, :d, :r, :u)'
            )->execute([':id' => $id, ':d' => $delta, ':r' => $reason, ':u' => $user['id']]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        Audit::log($this->db, (int) $user['id'], 'stock_adjusted', 'product', (string) $id,
            ['delta' => $delta, 'reason' => $reason], Auth::ip());
        $this->back('ok', 'Estoque ajustado.');
    }

    /** @return array|string Dados válidos ou mensagem de erro. */
    private function validatedProduct(): array|string
    {
        $barcode = Http::input('barcode', 64);
        $name = Http::input('name', 160);
        $price = Http::input('price', 12);
        $minStock = filter_var($_POST['min_stock'] ?? '0', FILTER_VALIDATE_INT);
        $active = isset($_POST['active']) ? 1 : 0;

        if ($barcode === '' || !ctype_digit($barcode)) {
            return 'Código de barras deve conter apenas números.';
        }
        if (mb_strlen($name) < 2) {
            return 'Informe o nome do produto.';
        }
        $normalized = str_replace(',', '.', $price);
        if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $normalized) || (float) $normalized <= 0) {
            return 'Preço inválido. Use valores como 18,90.';
        }
        if ($minStock === false || $minStock < 0) {
            return 'Estoque mínimo inválido.';
        }
        return [
            'barcode' => $barcode,
            'name' => $name,
            'price_cents' => (int) round((float) $normalized * 100),
            'stock' => 0,
            'min_stock' => $minStock,
            'active' => $active,
        ];
    }

    private function findProduct(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT barcode, name, price_cents, min_stock, active FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private function back(string $type, string $message): void
    {
        Http::flash($type, $message);
        Http::redirect('/admin/produtos');
    }
}
