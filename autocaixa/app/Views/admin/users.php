<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Usuários</h1>
    <p class="muted">Quem pode entrar no painel e o que cada pessoa pode fazer.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" data-open-dialog="dlg-user">Novo usuário</button>
  </div>
</div>

<section class="card card-flush" aria-label="Lista de usuários">
  <div class="table-wrap">
    <table class="table table-responsive">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Último acesso</th><th><span class="sr-only">Ações</span></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $active = (int) $r['active'] === 1; ?>
        <tr class="<?= $active ? '' : 'inactive' ?>">
          <td data-label="Nome" class="row-title"><?= $e($r['name']) ?><?= (int) $r['id'] === (int) $user['id'] ? ' (você)' : '' ?></td>
          <td data-label="E-mail"><?= $e($r['email']) ?></td>
          <td data-label="Perfil"><span class="badge badge-primary"><?= $e($roleLabel((string) $r['role'])) ?></span></td>
          <td data-label="Situação"><?= $active ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge">Inativo</span>' ?></td>
          <td data-label="Último acesso" class="nowrap"><?= $e($r['last_login_at'] ?? 'nunca') ?></td>
          <td data-label="">
            <?php if ((int) $r['id'] !== (int) $user['id']): ?>
            <div class="table-actions">
              <form method="post" action="/admin/usuarios/<?= $e($r['id']) ?>/situacao"
                <?php if ($active): ?>data-confirm="Desativar <?= $e($r['name']) ?>?" data-confirm-text="A pessoa não conseguirá mais entrar no painel. Você pode reativar a conta depois." data-confirm-label="Desativar usuário"<?php endif; ?>>
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button type="submit" class="btn btn-sm <?= $active ? 'btn-danger' : 'btn-secondary' ?>"><?= $active ? 'Desativar' : 'Reativar' ?></button>
              </form>
            </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<dialog class="modal" id="dlg-user" aria-labelledby="dlg-user-title">
  <form method="post" action="/admin/usuarios" class="modal-form">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <div class="modal-head">
      <h2 id="dlg-user-title">Novo usuário</h2>
      <button type="button" class="icon-btn" data-close-dialog aria-label="Fechar"><?= $icon('x') ?></button>
    </div>
    <label class="field"><span>Nome</span><input name="name" required maxlength="120" autocomplete="off"></label>
    <label class="field"><span>E-mail</span><input name="email" type="email" required maxlength="160" autocomplete="off"></label>
    <label class="field"><span>Perfil</span>
      <select name="role">
        <option value="cashier">Operador (somente vendas)</option>
        <option value="manager">Gerente (produtos, estoque, relatórios)</option>
        <option value="admin">Administrador</option>
      </select>
    </label>
    <label class="field"><span>Senha inicial</span>
      <span class="input-wrap">
        <input name="password" type="password" required minlength="12" autocomplete="new-password" id="new-user-password">
        <button type="button" class="icon-btn" data-toggle-password="new-user-password" aria-label="Mostrar senha"><?= $icon('eye') ?></button>
      </span>
      <span class="help">Pelo menos 12 caracteres. Peça para a pessoa trocar no primeiro acesso.</span>
    </label>
    <div class="modal-foot">
      <button type="button" class="btn btn-secondary" data-close-dialog>Cancelar</button>
      <button type="submit" class="btn btn-primary" data-loading-text="Criando...">Criar usuário</button>
    </div>
  </form>
</dialog>
<?php require __DIR__ . '/_footer.php'; ?>
