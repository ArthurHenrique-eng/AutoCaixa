<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Http;
use App\Core\View;
use PDO;

/** Vendas, cancelamentos, falhas e relatórios. */
final class AdminSalesController
{
    public function __construct(private PDO $db, private AdminController $auth) {}

    public function sales(): void
    {
        $user = $this->auth->requireUser('sales.view');
        if ($user === null) return;

        $from = Http::validDate($_GET['from'] ?? null) ?? date('Y-m-d');
        $to = Http::validDate($_GET['to'] ?? null) ?? date('Y-m-d');
        $status = (string) ($_GET['status'] ?? '');
        $allowed = ['', 'awaiting_payment', 'paid', 'failed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $status = '';
        }

        $sql = "SELECT s.id, s.status, s.total_cents, s.created_at,
                       (SELECT p.method FROM payments p WHERE p.sale_id = s.id ORDER BY p.id DESC LIMIT 1) AS method
                FROM sales s
                WHERE DATE(s.created_at) BETWEEN :from AND :to";
        $params = [':from' => $from, ':to' => $to];
        if ($status !== '') {
            $sql .= ' AND s.status = :status';
            $params[':status'] = $status;
        }
        $sql .= ' ORDER BY s.id DESC LIMIT 500';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        View::render('admin/sales', [
            'user' => $user, 'rows' => $stmt->fetchAll(), 'from' => $from, 'to' => $to, 'status' => $status,
        ]);
    }

    public function saleDetail(int $id): void
    {
        $user = $this->auth->requireUser('sales.view');
        if ($user === null) return;

        $stmt = $this->db->prepare('SELECT id, status, total_cents, created_at, updated_at FROM sales WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $sale = $stmt->fetch();
        if ($sale === false) {
            View::render('error', ['message' => 'Venda não encontrada.'], 404);
            return;
        }
        $items = $this->db->prepare(
            'SELECT product_name, unit_price_cents, quantity, subtotal_cents FROM sale_items WHERE sale_id = :id'
        );
        $items->execute([':id' => $id]);
        $payments = $this->db->prepare(
            'SELECT id, method, status, amount_cents, provider, created_at FROM payments WHERE sale_id = :id ORDER BY id'
        );
        $payments->execute([':id' => $id]);

        View::render('admin/sale_detail', [
            'user' => $user, 'sale' => $sale, 'items' => $items->fetchAll(), 'payments' => $payments->fetchAll(),
        ]);
    }

    /** Cancela somente vendas que ainda não foram pagas. Estorno de venda paga depende da integração bancária. */
    public function saleCancel(int $id): void
    {
        $user = $this->auth->requireUser('sales.cancel');
        if ($user === null) return;
        if (!Http::formCsrfOk()) {
            Http::flash('error', 'Sessão expirada. Tente novamente.');
            Http::redirect('/admin/vendas/' . $id);
            return;
        }

        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare(
                "UPDATE sales SET status = 'cancelled' WHERE id = :id AND status = 'awaiting_payment'"
            );
            $upd->execute([':id' => $id]);
            if ($upd->rowCount() === 0) {
                $this->db->rollBack();
                Http::flash('error', 'Somente vendas aguardando pagamento podem ser canceladas. Vendas pagas dependem de estorno.');
                Http::redirect('/admin/vendas/' . $id);
                return;
            }
            $this->db->prepare("UPDATE payments SET status = 'cancelled' WHERE sale_id = :id AND status = 'pending'")
                ->execute([':id' => $id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        Audit::log($this->db, (int) $user['id'], 'sale_cancelled', 'sale', (string) $id, null, Auth::ip());
        Http::flash('ok', 'Venda cancelada. Nenhum estoque foi alterado.');
        Http::redirect('/admin/vendas/' . $id);
    }

    public function report(): void
    {
        $user = $this->auth->requireUser('reports.view');
        if ($user === null) return;
        [$from, $to] = $this->period();
        $rows = $this->reportRows($from, $to);
        $totalCents = array_sum(array_column($rows, 'revenue_cents'));
        View::render('admin/report', [
            'user' => $user, 'rows' => $rows, 'from' => $from, 'to' => $to, 'totalCents' => $totalCents,
        ]);
    }

    public function reportCsv(): void
    {
        $user = $this->auth->requireUser('reports.view');
        if ($user === null) return;
        [$from, $to] = $this->period();
        $rows = $this->reportRows($from, $to);

        Audit::log($this->db, (int) $user['id'], 'report_exported', 'report', null, ['from' => $from, 'to' => $to], Auth::ip());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vendas-' . $from . '-a-' . $to . '.csv"');
        header('Cache-Control: no-store');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos
        fputcsv($out, ['Produto', 'Quantidade', 'Receita'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [$r['product_name'], $r['quantity'], number_format($r['revenue_cents'] / 100, 2, ',', '')], ';');
        }
        fclose($out);
    }

    /** @return array{0:string,1:string} */
    private function period(): array
    {
        $from = Http::validDate($_GET['from'] ?? null) ?? date('Y-m-01');
        $to = Http::validDate($_GET['to'] ?? null) ?? date('Y-m-d');
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to];
    }

    /** Somente vendas pagas entram no relatório. */
    private function reportRows(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT si.product_name, SUM(si.quantity) AS quantity, SUM(si.subtotal_cents) AS revenue_cents
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             WHERE s.status = 'paid' AND DATE(s.updated_at) BETWEEN :from AND :to
             GROUP BY si.product_name
             ORDER BY revenue_cents DESC"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll();
    }
}
