<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Vendas</h1>
    <p class="muted">Consulte as vendas por período e situação.</p>
  </div>
</div>

<form method="get" action="/admin/vendas" class="card filters">
  <label class="field"><span>De</span><input type="date" name="from" value="<?= $e($from) ?>"></label>
  <label class="field"><span>Até</span><input type="date" name="to" value="<?= $e($to) ?>"></label>
  <label class="field"><span>Situação</span>
    <select name="status">
      <option value="">Todas</option>
      <?php foreach (['awaiting_payment' => 'Aguardando pagamento', 'paid' => 'Paga', 'failed' => 'Falha', 'cancelled' => 'Cancelada', 'expired' => 'Expirada'] as $k => $label): ?>
        <option value="<?= $e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= $e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn btn-primary" data-loading-text="Filtrando...">Filtrar</button>
</form>

<section class="card card-flush" aria-label="Lista de vendas">
  <?php if ($rows === []): ?>
    <div class="empty-state">
      <strong>Nenhuma venda encontrada</strong>
      <span>Não há vendas neste período. Mude as datas ou a situação para ver outras.</span>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-responsive">
        <thead><tr><th>Nº</th><th>Data</th><th>Forma</th><th class="num">Total</th><th>Situação</th><th><span class="sr-only">Ações</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $s): ?>
          <tr>
            <td data-label="Nº" class="row-title">#<?= $e($s['id']) ?></td>
            <td data-label="Data" class="nowrap"><?= $e($s['created_at']) ?></td>
            <td data-label="Forma"><?= $e($methodLabel($s['method'])) ?></td>
            <td data-label="Total" class="num"><?= $e($money((int) $s['total_cents'])) ?></td>
            <td data-label="Situação"><?= $statusBadge((string) $s['status']) ?></td>
            <td data-label=""><div class="table-actions"><a class="btn btn-secondary btn-sm" href="/admin/vendas/<?= $e($s['id']) ?>">Ver detalhes</a></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
