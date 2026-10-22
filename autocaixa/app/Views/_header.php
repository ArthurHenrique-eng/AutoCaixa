<?php /** @var callable $e @var string $csrf @var array $config */ ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $e($config['app']['name']) ?></title>
  <meta name="csrf-token" content="<?= $e($csrf) ?>">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<header class="topbar">
  <span class="store"><?= $e($config['app']['store']) ?></span>
  <span class="app-name"><?= $e($config['app']['name']) ?></span>
</header>
<?php if (($config['payment']['driver'] ?? '') === 'simulated'): ?>
  <div class="banner-simulation" role="status">
    MODO SIMULAÇÃO: nenhuma cobrança real é feita neste ambiente.
  </div>
<?php endif; ?>
<main class="container">
