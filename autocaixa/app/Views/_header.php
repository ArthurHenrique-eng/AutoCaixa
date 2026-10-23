<?php /** @var callable $e @var string $csrf @var array $config */
$icon = static fn(string $name): string =>
    '<svg class="icon" aria-hidden="true" focusable="false"><use href="/assets/icons.svg#' . $name . '"></use></svg>';
?>
<!doctype html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $e($config['app']['name']) ?></title>
  <meta name="csrf-token" content="<?= $e($csrf) ?>">
  <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="totem">
<a class="skip-link" href="#main">Pular para o conteúdo</a>
<header class="topbar">
  <a class="brand" href="/">
    <span class="brand-mark"><?= $icon('shopping-cart') ?></span>
    <span class="brand-text"><strong><?= $e($config['app']['store']) ?></strong><small><?= $e($config['app']['name']) ?></small></span>
  </a>
</header>
<?php if (($config['payment']['driver'] ?? '') === 'simulated'): ?>
  <div class="banner-simulation" role="status">
    <?= $icon('triangle-alert') ?>
    <span>MODO SIMULAÇÃO: nenhuma cobrança real é feita neste ambiente.</span>
  </div>
<?php endif; ?>
<main class="container" id="main">
