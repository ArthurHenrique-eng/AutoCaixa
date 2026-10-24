<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Auditoria</h1>
    <p class="muted">Últimos 300 registros. Senhas e dados sensíveis nunca são gravados aqui.</p>
  </div>
</div>

<section class="card card-flush" aria-label="Registros de auditoria">
  <?php if ($rows === []): ?>
    <div class="empty-state">
      <strong>Nenhum registro ainda</strong>
      <span>As ações sensíveis feitas no painel aparecerão aqui.</span>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-responsive">
        <thead><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>Detalhes</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Data" class="nowrap"><?= $e($r['created_at']) ?></td>
            <td data-label="Usuário"><?= $e($r['email'] ?? '—') ?></td>
            <td data-label="Ação"><?= $e($r['action']) ?></td>
            <td data-label="Entidade"><?= $e($r['entity']) ?> <?= $e($r['entity_id']) ?></td>
            <td data-label="Detalhes"><code class="code-cell"><?= $e($r['details'] ?? '') ?></code></td>
            <td data-label="IP" class="nowrap"><?= $e($r['ip'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
