<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Início</h1>
    <p class="muted">Resumo de hoje, <?= $e(date('d/m/Y')) ?>.</p>
  </div>
</div>

<div class="kpi-grid">
  <div class="card kpi"><span class="kpi-label">Vendas pagas hoje</span><strong class="kpi-value"><?= $e($todayCount) ?></strong></div>
  <div class="card kpi"><span class="kpi-label">Total de hoje</span><strong class="kpi-value"><?= $e($money($todayTotal)) ?></strong></div>
  <div class="card kpi"><span class="kpi-label">Aguardando pagamento</span><strong class="kpi-value"><?= $e($awaiting) ?></strong></div>
  <div class="card kpi <?= $failedToday > 0 ? 'is-alert' : '' ?>"><span class="kpi-label">Falhas hoje</span><strong class="kpi-value"><?= $e($failedToday) ?></strong></div>
</div>

<section class="card card-flush" aria-labelledby="low-stock-title">
  <div class="card-head">
    <h2 id="low-stock-title">Estoque baixo</h2>
    <?php if ($lowStock !== []): ?><span class="badge badge-warning"><?= $e(count($lowStock)) ?> <?= count($lowStock) === 1 ? 'produto' : 'produtos' ?></span><?php endif; ?>
  </div>
  <?php if ($lowStock === []): ?>
    <div class="empty-state">
      <strong>Estoque em dia</strong>
      <span>Nenhum produto abaixo do estoque mínimo.</span>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-responsive">
        <thead><tr><th>Produto</th><th>Código</th><th class="num">Estoque</th><th class="num">Mínimo</th><th>Situação</th></tr></thead>
        <tbody>
        <?php foreach ($lowStock as $p): ?>
          <tr>
            <td data-label="Produto" class="row-title"><?= $e($p['name']) ?></td>
            <td data-label="Código"><?= $e($p['barcode']) ?></td>
            <td data-label="Estoque" class="num"><?= $e($p['stock']) ?></td>
            <td data-label="Mínimo" class="num"><?= $e($p['min_stock']) ?></td>
            <td data-label="Situação"><?php if ((int) $p['stock'] === 0): ?><span class="badge badge-danger">Sem estoque</span><?php else: ?><span class="badge badge-warning">Estoque baixo</span><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (\App\Core\Permissions::allows($user['role'], 'products.manage')): ?>
      <div class="toolbar"><span class="field-note">Registre a entrada de mercadoria em Produtos, no botão "Estoque".</span><a class="btn btn-secondary btn-sm" href="/admin/produtos">Ir para Produtos</a></div>
    <?php endif; ?>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
