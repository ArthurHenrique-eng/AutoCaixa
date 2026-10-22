<?php /** @var callable $e @var string $csrf @var array $config @var ?array $user @var callable $money */
use App\Core\Permissions;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Painel — <?= $e($config['app']['name']) ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="admin">
<header class="topbar">
  <span class="store"><?= $e($config['app']['store']) ?> · Painel</span>
  <?php if ($user !== null): ?>
    <nav class="admin-nav" aria-label="Painel">
      <?php if (\App\Core\Permissions::allows($user['role'], 'dashboard.view')): ?><a href="/admin">Início</a><?php endif; ?>
      <?php if (\App\Core\Permissions::allows($user['role'], 'sales.view')): ?><a href="/admin/vendas">Vendas</a><?php endif; ?>
      <?php if (\App\Core\Permissions::allows($user['role'], 'products.manage')): ?><a href="/admin/produtos">Produtos</a><?php endif; ?>
      <?php if (\App\Core\Permissions::allows($user['role'], 'reports.view')): ?><a href="/admin/relatorios">Relatórios</a><?php endif; ?>
      <?php if (\App\Core\Permissions::allows($user['role'], 'users.manage')): ?><a href="/admin/usuarios">Usuários</a><?php endif; ?>
      <?php if (\App\Core\Permissions::allows($user['role'], 'audit.view')): ?><a href="/admin/auditoria">Auditoria</a><?php endif; ?>
    </nav>
    <form method="post" action="/admin/sair" class="inline">
      <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
      <button type="submit" class="btn btn-small">Sair (<?= $e($user['name']) ?>)</button>
    </form>
  <?php endif; ?>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="flash flash-<?= $e($flash['type']) ?>" role="status"><?= $e($flash['message']) ?></div>
<?php endif; ?>
