<?php require __DIR__ . '/_header.php'; ?>
<h1>Auditoria</h1>
<p class="muted">Últimos 300 registros. Senhas e dados sensíveis nunca são gravados aqui.</p>
<table class="table">
  <thead><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>Detalhes</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= $e($r['created_at']) ?></td>
      <td><?= $e($r['email'] ?? '—') ?></td>
      <td><?= $e($r['action']) ?></td>
      <td><?= $e($r['entity']) ?> <?= $e($r['entity_id']) ?></td>
      <td class="small"><?= $e($r['details'] ?? '') ?></td>
      <td><?= $e($r['ip'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_footer.php'; ?>
