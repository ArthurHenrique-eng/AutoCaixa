<?php /** @var callable $e @var string $csrf @var array $config @var ?string $error */ $user = null; $money = fn($c) => ''; require __DIR__ . '/_header.php'; ?>
<section class="card auth-card">
  <header>
    <strong><?= $e($config['app']['store']) ?></strong>
    <h1>Entrar no painel</h1>
    <p class="muted">Use o e-mail e a senha da sua conta.</p>
  </header>
  <?php if ($simulated): ?>
    <div class="banner-simulation" role="status"><span>MODO SIMULAÇÃO: nenhuma cobrança real é feita neste ambiente.</span></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error" role="alert"><span class="alert-body"><?= $e($error) ?></span></div>
  <?php endif; ?>
  <form method="post" action="/admin/login" class="form-stack">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label class="field"><span>E-mail</span>
      <input type="email" name="email" required autocomplete="username" maxlength="160" autofocus>
    </label>
    <label class="field"><span>Senha</span>
      <span class="input-wrap">
        <input type="password" name="password" required autocomplete="current-password" id="password">
        <button type="button" class="icon-btn" data-toggle-password="password" aria-label="Mostrar senha"><?= $icon('eye') ?></button>
      </span>
    </label>
    <button type="submit" class="btn btn-primary btn-lg btn-block" data-loading-text="Entrando...">Entrar</button>
  </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
