<?php require __DIR__ . '/_header.php'; ?>
<h1>Relatório de vendas</h1>
<form method="get" action="/admin/relatorios" class="filters">
  <label>De <input type="date" name="from" value="<?= $e($from) ?>"></label>
  <label>Até <input type="date" name="to" value="<?= $e($to) ?>"></label>
  <button type="submit" class="btn btn-secondary">Atualizar</button>
  <a class="btn btn-primary" href="/admin/relatorios/csv?from=<?= $e($from) ?>&amp;to=<?= $e($to) ?>">Exportar CSV</a>
</form>
<p>Total no período: <strong><?= $e($money((int) $totalCents)) ?></strong> (somente vendas pagas)</p>
<table class="table">
  <thead><tr><th>Produto</th><th>Quantidade</th><th>Receita</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= $e($r['product_name']) ?></td><td><?= $e($r['quantity']) ?></td><td><?= $e($money((int) $r['revenue_cents'])) ?></td></tr>
  <?php endforeach; ?>
  <?php if ($rows === []): ?><tr><td colspan="3" class="muted">Sem vendas pagas no período.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_footer.php'; ?>
