<?php /** @var callable $e @var string $csrf @var array $config @var ?array $user @var callable $money */
use App\Core\Permissions;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$icon = static fn(string $name): string =>
    '<svg class="icon" aria-hidden="true" focusable="false"><use href="/assets/icons.svg#' . $name . '"></use></svg>';

/** Selo de situação da venda. Texto sempre visível; a cor só reforça. */
$statusBadge = static function (string $status) use ($e): string {
    $map = [
        'awaiting_payment' => ['info', 'Aguardando pagamento'],
        'paid'             => ['ok', 'Paga'],
        'failed'           => ['danger', 'Falha'],
        'cancelled'        => ['muted', 'Cancelada'],
        'expired'          => ['muted', 'Expirada'],
    ];
    [$tone, $label] = $map[$status] ?? ['muted', $status];
    return '<span class="badge badge-' . $tone . '">' . $e($label) . '</span>';
};
$payBadge = static function (string $status) use ($e): string {
    $map = [
        'pending'   => ['info', 'Pendente'],
        'approved'  => ['ok', 'Aprovado'],
        'declined'  => ['danger', 'Recusado'],
        'cancelled' => ['muted', 'Cancelado'],
        'expired'   => ['muted', 'Expirado'],
        'failed'    => ['danger', 'Falhou'],
        'unknown'   => ['warning', 'Resultado desconhecido'],
    ];
    [$tone, $label] = $map[$status] ?? ['muted', $status];
    return '<span class="badge badge-' . $tone . '">' . $e($label) . '</span>';
};
$methodLabel = static fn(?string $m): string => $m === 'pix' ? 'Pix' : ($m === 'card' ? 'Cartão' : '—');
$roleLabel = static fn(string $r): string => ['admin' => 'Administrador', 'manager' => 'Gerente', 'cashier' => 'Operador'][$r] ?? $r;

$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/admin'), PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? (rtrim($currentPath, '/') ?: '/') : '/admin';
$navLinks = [
    ['/admin',            'dashboard.view',  'layout-dashboard', 'Início'],
    ['/admin/vendas',     'sales.view',      'receipt-text',     'Vendas'],
    ['/admin/produtos',   'products.manage', 'package',          'Produtos'],
    ['/admin/relatorios', 'reports.view',    'chart-column',     'Relatórios'],
    ['/admin/usuarios',   'users.manage',    'users',            'Usuários'],
    ['/admin/auditoria',  'audit.view',      'history',          'Auditoria'],
];
$simulated = ($config['payment']['driver'] ?? '') === 'simulated';
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
<?php if ($user !== null): ?>
<div class="shell">
  <aside class="sidebar" id="sidebar" aria-label="Menu do painel">
    <a class="sidebar-brand" href="/admin">
      <strong><?= $e($config['app']['store']) ?></strong>
      <span>Painel administrativo</span>
    </a>
    <nav class="side-nav" aria-label="Painel">
      <?php foreach ($navLinks as [$href, $perm, $ic, $label]):
          if (!Permissions::allows($user['role'], $perm)) continue;
          $active = $href === '/admin' ? $currentPath === '/admin' : ($currentPath === $href || str_starts_with($currentPath, $href . '/')); ?>
        <a class="side-link" href="<?= $e($href) ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= $icon($ic) ?><?= $e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-footer">
      <div class="user-chip">
        <strong><?= $e($user['name']) ?></strong>
        <span><?= $e($roleLabel((string) $user['role'])) ?></span>
      </div>
      <form method="post" action="/admin/sair">
        <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
        <button type="submit" class="btn btn-secondary btn-block">Sair</button>
      </form>
    </div>
  </aside>
  <div class="scrim" data-nav-close></div>
  <div class="shell-main">
    <header class="mobile-bar">
      <button type="button" class="icon-btn" data-nav-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menu"><?= $icon('menu') ?></button>
      <strong><?= $e($config['app']['store']) ?></strong>
    </header>
    <?php if ($simulated): ?>
      <div class="banner-simulation" role="status"><span>MODO SIMULAÇÃO: nenhuma cobrança real é feita neste ambiente.</span></div>
    <?php endif; ?>
    <main class="page" id="main">
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'ok' ? 'ok' : 'error' ?>" role="status">
          <span class="alert-body"><?= $e($flash['message']) ?></span>
          <button type="button" class="icon-btn" data-dismiss aria-label="Fechar aviso"><?= $icon('x') ?></button>
        </div>
      <?php endif; ?>
<?php else: ?>
<div class="auth-shell">
  <main id="main">
<?php endif; ?>
