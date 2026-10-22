<?php require __DIR__ . '/_header.php'; ?>
<section class="error-box">
  <h1>Atenção</h1>
  <p><?= $e($message) ?></p>
  <a class="btn btn-primary btn-xl" href="/">Voltar ao início</a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
