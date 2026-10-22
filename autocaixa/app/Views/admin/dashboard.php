<?php require __DIR__ . '/_header.php'; ?>
<h1>Início</h1>
<div class="cards">
  <div class="card"><span>Vendas pagas hoje</span><strong><?= $e($todayCount) ?></strong></div>
  <div class="card"><span>Total de hoje</span><strong><?= $e($money($todayTotal)) ?></strong></div>
  <div class="card"><span>Aguardando pagamento</span><strong><?= $e($awaiting) ?></strong></div>
  <div class="card warn"><span>Falhas hoje</span><strong><?= $e($failedToday) ?></strong></div>
</div>
<h2>Estoque baixo</h2>
<?php if ($lowStock === []): ?>
  <p class="muted">Nenhum produto abaixo do estoque mínimo.</p>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Produto</th><th>Código</th><th>Estoque</th><th>Mínimo</th></tr></thead>
    <tbody>
    <?php foreach ($lowStock as $p): ?>
      <tr><td><?= $e($p['name']) ?></td><td><?= $e($p['barcode']) ?></td><td><?= $e($p['stock']) ?></td><td><?= $e($p['min_stock']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
