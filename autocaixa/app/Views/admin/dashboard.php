<?php require __DIR__ . '/_header.php'; ?>
<section class="admin-page-head">
  <div>
    <p class="admin-eyebrow">PAINEL DE CONTROLE</p>
    <h1>Visão geral</h1>
    <p class="admin-subtitle">Acompanhe as vendas e o estado do estoque da loja.</p>
  </div>
  <div class="admin-datebox">
    <span class="admin-date-icon"><?= $adminIcon('clock') ?></span>
    <span><small>Hoje</small><strong><?= $e(date('d/m/Y')) ?></strong></span>
  </div>
</section>

<section class="admin-kpis" aria-label="Indicadores de hoje">
  <article class="admin-stat-card">
    <div class="admin-stat-heading"><span>Vendas pagas hoje</span><span class="admin-stat-icon tone-primary"><?= $adminIcon('receipt-text') ?></span></div>
    <strong class="admin-stat-value"><?= $e($todayCount) ?></strong>
    <p class="admin-stat-note">Vendas concluídas</p>
  </article>
  <article class="admin-stat-card">
    <div class="admin-stat-heading"><span>Total de hoje</span><span class="admin-stat-icon tone-ok"><?= $adminIcon('banknote') ?></span></div>
    <strong class="admin-stat-value"><?= $e($money($todayTotal)) ?></strong>
    <p class="admin-stat-note">Receita de vendas pagas</p>
  </article>
  <article class="admin-stat-card">
    <div class="admin-stat-heading"><span>Aguardando pagamento</span><span class="admin-stat-icon tone-info"><?= $adminIcon('clock') ?></span></div>
    <strong class="admin-stat-value"><?= $e($awaiting) ?></strong>
    <p class="admin-stat-note">Vendas em aberto</p>
  </article>
  <article class="admin-stat-card">
    <div class="admin-stat-heading"><span>Falhas hoje</span><span class="admin-stat-icon tone-danger"><?= $adminIcon('triangle-alert') ?></span></div>
    <strong class="admin-stat-value"><?= $e($failedToday) ?></strong>
    <p class="admin-stat-note">Pagamentos não concluídos</p>
  </article>
</section>

<section class="admin-section" aria-labelledby="low-stock-heading">
  <div class="admin-section-head">
    <div>
      <p class="admin-eyebrow">INVENTÁRIO</p>
      <h2 id="low-stock-heading">Estoque baixo</h2>
      <p class="admin-subtitle">Produtos que precisam de atenção para evitar falta na loja.</p>
    </div>
    <span class="admin-tag <?= $lowStock === [] ? 'is-ok' : 'is-warning' ?>">
      <?= $lowStock === [] ? 'Sem alertas' : count($lowStock) . (count($lowStock) === 1 ? ' produto' : ' produtos') ?>
    </span>
  </div>

  <div class="admin-panel">
    <?php if ($lowStock === []): ?>
      <div class="admin-empty-state">
        <span class="admin-empty-icon"><?= $adminIcon('circle-check') ?></span>
        <div>
          <strong>Estoque sob controle</strong>
          <p>Nenhum produto está abaixo do estoque mínimo.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="admin-table-scroll">
        <table class="table admin-stock-table">
          <thead><tr><th>Produto</th><th>Código de barras</th><th>Estoque atual</th><th>Estoque mínimo</th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $p): ?>
            <tr>
              <td><strong><?= $e($p['name']) ?></strong></td>
              <td><span class="admin-code"><?= $e($p['barcode']) ?></span></td>
              <td><span class="admin-stock-count is-low"><?= $e($p['stock']) ?></span></td>
              <td><?= $e($p['min_stock']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if (\App\Core\Permissions::allows($user['role'], 'products.manage')): ?>
        <div class="admin-panel-footer"><a class="btn btn-secondary btn-sm" href="/admin/produtos"><?= $adminIcon('package') ?>Gerenciar produtos</a></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
