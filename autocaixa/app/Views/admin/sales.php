<?php require __DIR__ . '/_header.php'; ?>
<h1>Vendas</h1>
<form method="get" action="/admin/vendas" class="filters">
  <label>De <input type="date" name="from" value="<?= $e($from) ?>"></label>
  <label>Até <input type="date" name="to" value="<?= $e($to) ?>"></label>
  <label>Situação
    <select name="status">
      <option value="">Todas</option>
      <?php foreach (['awaiting_payment' => 'Aguardando', 'paid' => 'Paga', 'failed' => 'Falha', 'cancelled' => 'Cancelada'] as $k => $label): ?>
        <option value="<?= $e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= $e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>
<table class="table">
  <thead><tr><th>Nº</th><th>Data</th><th>Forma</th><th>Total</th><th>Situação</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $s): ?>
    <tr>
      <td><?= $e($s['id']) ?></td>
      <td><?= $e($s['created_at']) ?></td>
      <td><?= $e($s['method'] === 'pix' ? 'Pix' : ($s['method'] === 'card' ? 'Cartão' : '—')) ?></td>
      <td><?= $e($money((int) $s['total_cents'])) ?></td>
      <td><?= $e(['awaiting_payment' => 'Aguardando', 'paid' => 'Paga', 'failed' => 'Falha', 'cancelled' => 'Cancelada'][$s['status']] ?? $s['status']) ?></td>
      <td><a href="/admin/vendas/<?= $e($s['id']) ?>">Detalhes</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if ($rows === []): ?><tr><td colspan="6" class="muted">Nenhuma venda no período.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_footer.php'; ?>
