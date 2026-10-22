<?php /** @var callable $e @var string $csrf @var array $config @var ?string $error */ $user = null; $money = fn($c) => ''; require __DIR__ . '/_header.php'; ?>
<section class="login-box">
  <h1>Entrar no painel</h1>
  <?php if ($error): ?><p class="feedback error" role="alert"><?= $e($error) ?></p><?php endif; ?>
  <form method="post" action="/admin/login" class="form-stack">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label>E-mail <input type="email" name="email" required autocomplete="username" maxlength="160"></label>
    <label>Senha <input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary btn-xl">Entrar</button>
  </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
