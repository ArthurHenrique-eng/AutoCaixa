<?php require __DIR__ . '/_header.php'; ?>
<h1>Usuários</h1>
<details class="panel" open>
  <summary>Novo usuário</summary>
  <form method="post" action="/admin/usuarios" class="form-grid">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label>Nome <input name="name" required maxlength="120"></label>
    <label>E-mail <input name="email" type="email" required maxlength="160"></label>
    <label>Perfil
      <select name="role">
        <option value="cashier">Operador (somente vendas)</option>
        <option value="manager">Gerente (produtos, estoque, relatórios)</option>
        <option value="admin">Administrador</option>
      </select>
    </label>
    <label>Senha inicial (mín. 12) <input name="password" type="password" required minlength="12" autocomplete="new-password"></label>
    <button type="submit" class="btn btn-primary">Criar</button>
  </form>
</details>
<table class="table">
  <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Último acesso</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= $e($r['name']) ?></td>
      <td><?= $e($r['email']) ?></td>
      <td><?= $e(['admin' => 'Administrador', 'manager' => 'Gerente', 'cashier' => 'Operador'][$r['role']] ?? $r['role']) ?></td>
      <td><?= (int) $r['active'] === 1 ? 'Ativo' : 'Inativo' ?></td>
      <td><?= $e($r['last_login_at'] ?? 'nunca') ?></td>
      <td>
        <?php if ((int) $r['id'] !== (int) $user['id']): ?>
        <form method="post" action="/admin/usuarios/<?= $e($r['id']) ?>/situacao" class="inline">
          <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
          <button type="submit" class="btn btn-small"><?= (int) $r['active'] === 1 ? 'Desativar' : 'Reativar' ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_footer.php'; ?>
