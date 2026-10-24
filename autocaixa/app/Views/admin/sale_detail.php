<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <a class="btn btn-secondary btn-sm" href="/admin/vendas">Voltar às vendas</a>
    <h1>Venda nº <?= $e($sale['id']) ?></h1>
    <p class="muted">Criada em <?= $e($sale['created_at']) ?></p>
  </div>
  <div class="page-actions"><?= $statusBadge((string) $sale['status']) ?></div>
</div>

<section class="card card-flush" aria-labelledby="items-title">
  <div class="card-head"><h2 id="items-title">Itens da venda</h2></div>
  <div class="table-wrap">
    <table class="table table-responsive">
      <thead><tr><th>Produto</th><th class="num">Qtd</th><th class="num">Preço</th><th class="num">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td data-label="Produto" class="row-title"><?= $e($i['product_name']) ?></td>
          <td data-label="Qtd" class="num"><?= $e($i['quantity']) ?></td>
          <td data-label="Preço" class="num"><?= $e($money((int) $i['unit_price_cents'])) ?></td>
          <td data-label="Subtotal" class="num"><?= $e($money((int) $i['subtotal_cents'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><td colspan="3">Total</td><td class="num"><?= $e($money((int) $sale['total_cents'])) ?></td></tr></tfoot>
    </table>
  </div>
</section>

<section class="card card-flush" aria-labelledby="pay-title">
  <div class="card-head"><h2 id="pay-title">Pagamentos</h2></div>
  <div class="table-wrap">
    <table class="table table-responsive">
      <thead><tr><th>Nº</th><th>Forma</th><th>Situação</th><th class="num">Valor</th><th>Provedor</th><th>Data</th></tr></thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td data-label="Nº">#<?= $e($p['id']) ?></td>
          <td data-label="Forma"><?= $e($methodLabel($p['method'])) ?></td>
          <td data-label="Situação"><?= $payBadge((string) $p['status']) ?></td>
          <td data-label="Valor" class="num"><?= $e($money((int) $p['amount_cents'])) ?></td>
          <td data-label="Provedor"><?= $e($p['provider']) ?></td>
          <td data-label="Data" class="nowrap"><?= $e($p['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if ($sale['status'] === 'awaiting_payment' && \App\Core\Permissions::allows($user['role'], 'sales.cancel')): ?>
  <section class="card card-stack">
    <h2>Cancelar venda</h2>
    <p class="muted">Só é possível cancelar vendas que ainda aguardam pagamento. A reserva de estoque da venda é liberada.</p>
    <form method="post" action="/admin/vendas/<?= $e($sale['id']) ?>/cancelar" class="page-actions"
          data-confirm="Cancelar a venda nº <?= $e($sale['id']) ?>?"
          data-confirm-text="Esta ação não pode ser desfeita. A reserva de estoque desta venda será liberada."
          data-confirm-label="Cancelar venda">
      <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
      <button type="submit" class="btn btn-danger" data-loading-text="Cancelando...">Cancelar venda</button>
    </form>
  </section>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
