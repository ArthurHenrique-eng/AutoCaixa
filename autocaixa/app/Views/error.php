<?php require __DIR__ . '/_header.php';
$inAdmin = str_starts_with((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/admin');
?>
<section class="card error-box">
  <span class="status-icon" aria-hidden="true"><?= $icon('triangle-alert') ?></span>
  <h1>Atenção</h1>
  <p class="lead"><?= $e($message) ?></p>
  <a class="btn btn-primary btn-xl" href="<?= $inAdmin ? '/admin' : '/' ?>"><?= $icon('house') ?><?= $inAdmin ? 'Voltar ao painel' : 'Voltar ao início' ?></a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
