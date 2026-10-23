<?php /** @var callable $e @var string $csrf @var array $config @var ?array $user @var callable $money */
use App\Core\Permissions;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/admin'), PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? (rtrim($currentPath, '/') ?: '/') : '/admin';
$adminIcon = static fn(string $name): string =>
    '<svg class="icon" aria-hidden="true" focusable="false"><use href="/assets/icons.svg#' . $name . '"></use></svg>';
$adminInitial = '?';
if ($user !== null && preg_match('/^./us', (string) $user['name'], $initialMatch) === 1) {
    $adminInitial = $initialMatch[0];
}
$isAdminPage = static function (string $href) use ($currentPath): bool {
    if ($href === '/admin') {
        return $currentPath === '/admin';
    }
    return $currentPath === $href || str_starts_with($currentPath, $href . '/');
};
?>
<!doctype html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <title>Painel — <?= $e($config['app']['name']) ?></title>
  <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="admin">
<a class="skip-link" href="#main">Pular para o conteúdo</a>
<header class="topbar">
  <div class="admin-topbar-inner">
    <a class="admin-brand" href="/admin" aria-label="<?= $e($config['app']['store']) ?> — painel administrativo">
      <span class="admin-brand-mark"><?= $adminIcon('shopping-cart') ?></span>
      <span class="admin-brand-text">
        <strong><?= $e($config['app']['store']) ?></strong>
        <small>Painel administrativo</small>
      </span>
    </a>
    <?php if ($user !== null): ?>
      <nav class="admin-nav" aria-label="Navegação administrativa">
        <?php if (Permissions::allows($user['role'], 'dashboard.view')): ?>
          <a href="/admin" <?= $isAdminPage('/admin') ? 'aria-current="page"' : '' ?>><?= $adminIcon('layout-dashboard') ?><span>Início</span></a>
        <?php endif; ?>
        <?php if (Permissions::allows($user['role'], 'sales.view')): ?>
          <a href="/admin/vendas" <?= $isAdminPage('/admin/vendas') ? 'aria-current="page"' : '' ?>><?= $adminIcon('receipt-text') ?><span>Vendas</span></a>
        <?php endif; ?>
        <?php if (Permissions::allows($user['role'], 'products.manage')): ?>
          <a href="/admin/produtos" <?= $isAdminPage('/admin/produtos') ? 'aria-current="page"' : '' ?>><?= $adminIcon('package') ?><span>Produtos</span></a>
        <?php endif; ?>
        <?php if (Permissions::allows($user['role'], 'reports.view')): ?>
          <a href="/admin/relatorios" <?= $isAdminPage('/admin/relatorios') ? 'aria-current="page"' : '' ?>><?= $adminIcon('chart-column') ?><span>Relatórios</span></a>
        <?php endif; ?>
        <?php if (Permissions::allows($user['role'], 'users.manage')): ?>
          <a href="/admin/usuarios" <?= $isAdminPage('/admin/usuarios') ? 'aria-current="page"' : '' ?>><?= $adminIcon('users') ?><span>Usuários</span></a>
        <?php endif; ?>
        <?php if (Permissions::allows($user['role'], 'audit.view')): ?>
          <a href="/admin/auditoria" <?= $isAdminPage('/admin/auditoria') ? 'aria-current="page"' : '' ?>><?= $adminIcon('history') ?><span>Auditoria</span></a>
        <?php endif; ?>
      </nav>
      <div class="admin-account">
        <span class="admin-avatar" aria-hidden="true"><?= $e($adminInitial) ?></span>
        <span class="admin-account-name"><?= $e($user['name']) ?></span>
        <form method="post" action="/admin/sair" class="admin-logout-form">
          <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
          <button type="submit" class="btn btn-secondary btn-sm"><?= $adminIcon('log-out') ?><span>Sair</span></button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</header>
<main class="container admin-container" id="main">
<?php if ($flash): ?>
  <div class="flash flash-<?= $e($flash['type']) ?>" role="status"><?= $e($flash['message']) ?></div>
<?php endif; ?>
