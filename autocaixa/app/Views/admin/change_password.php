<?php require __DIR__ . '/_header.php'; ?>
<div class="page-head">
  <div>
    <h1>Trocar senha</h1>
    <p class="muted">Sua senha é provisória. Defina uma nova senha para continuar usando o painel.</p>
  </div>
</div>

<section class="card card-narrow">
  <?php if ($error): ?>
    <div class="alert alert-error" role="alert"><span class="alert-body"><?= $e($error) ?></span></div>
  <?php endif; ?>
  <form method="post" action="/admin/trocar-senha" class="form-stack">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label class="field"><span>Senha atual (provisória)</span>
      <input type="password" name="current_password" required autocomplete="current-password">
    </label>
    <label class="field"><span>Nova senha</span>
      <input type="password" name="new_password" required minlength="12" autocomplete="new-password">
      <span class="help">Pelo menos 12 caracteres.</span>
    </label>
    <label class="field"><span>Confirmar nova senha</span>
      <input type="password" name="new_password_confirmation" required minlength="12" autocomplete="new-password">
    </label>
    <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-text="Salvando...">Salvar nova senha</button>
  </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
