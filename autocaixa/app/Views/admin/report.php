<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Relatório de vendas</h1>
    <p class="muted">Somente vendas pagas entram no relatório.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-secondary" href="/admin/relatorios/csv?from=<?= $e($from) ?>&amp;to=<?= $e($to) ?>">Exportar CSV</a>
  </div>
</div>

<form method="get" action="/admin/relatorios" class="card filters">
  <label class="field"><span>De</span><input type="date" name="from" value="<?= $e($from) ?>"></label>
  <label class="field"><span>Até</span><input type="date" name="to" value="<?= $e($to) ?>"></label>
  <button type="submit" class="btn btn-primary" data-loading-text="Atualizando...">Atualizar</button>
</form>

<div class="card kpi"><span class="kpi-label">Total no período</span><strong class="kpi-value"><?= $e($money((int) $totalCents)) ?></strong></div>

<section class="card card-flush" aria-label="Vendas por produto">
  <?php if ($rows === []): ?>
    <div class="empty-state">
      <strong>Sem vendas pagas no período</strong>
      <span>Mude as datas para ver outros períodos.</span>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-responsive">
        <thead><tr><th>Produto</th><th class="num">Quantidade</th><th class="num">Receita</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Produto" class="row-title"><?= $e($r['product_name']) ?></td>
            <td data-label="Quantidade" class="num"><?= $e($r['quantity']) ?></td>
            <td data-label="Receita" class="num"><?= $e($money((int) $r['revenue_cents'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="2" data-label="">Total</td><td class="num" data-label="Receita"><?= $e($money((int) $totalCents)) ?></td></tr></tfoot>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
