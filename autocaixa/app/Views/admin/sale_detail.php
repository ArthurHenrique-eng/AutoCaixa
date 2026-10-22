<?php require __DIR__ . '/_header.php'; ?>
<h1>Venda nº <?= $e($sale['id']) ?></h1>
<p>Situação: <strong><?= $e(['awaiting_payment' => 'Aguardando pagamento', 'paid' => 'Paga', 'failed' => 'Falha', 'cancelled' => 'Cancelada'][$sale['status']] ?? $sale['status']) ?></strong></p>
<table class="table">
  <thead><tr><th>Produto</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
  <tbody>
  <?php foreach ($items as $i): ?>
    <tr><td><?= $e($i['product_name']) ?></td><td><?= $e($i['quantity']) ?></td><td><?= $e($money((int) $i['unit_price_cents'])) ?></td><td><?= $e($money((int) $i['subtotal_cents'])) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<p class="total">Total: <strong><?= $e($money((int) $sale['total_cents'])) ?></strong></p>

<h2>Pagamentos</h2>
<table class="table">
  <thead><tr><th>Nº</th><th>Forma</th><th>Situação</th><th>Valor</th><th>Provedor</th><th>Data</th></tr></thead>
  <tbody>
  <?php foreach ($payments as $p): ?>
    <tr><td><?= $e($p['id']) ?></td><td><?= $e($p['method']) ?></td><td><?= $e($p['status']) ?></td><td><?= $e($money((int) $p['amount_cents'])) ?></td><td><?= $e($p['provider']) ?></td><td><?= $e($p['created_at']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php if ($sale['status'] === 'awaiting_payment' && \App\Core\Permissions::allows($user['role'], 'sales.cancel')): ?>
  <form method="post" action="/admin/vendas/<?= $e($sale['id']) ?>/cancelar" onsubmit="return confirm('Cancelar esta venda?');">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <button type="submit" class="btn btn-danger">Cancelar venda</button>
  </form>
<?php endif; ?>
<p><a href="/admin/vendas">Voltar às vendas</a></p>
<?php require __DIR__ . '/_footer.php'; ?>
