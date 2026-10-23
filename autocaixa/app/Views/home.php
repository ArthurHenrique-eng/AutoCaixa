<?php require __DIR__ . '/_header.php'; ?>
<section class="welcome" data-page="home">
  <div class="hero">
    <h1>Compre sozinho em 3 passos</h1>
    <p class="lead">Passe os produtos no leitor, confira o total e pague.</p>
    <a class="btn btn-primary btn-xl" href="/comprar"><?= $icon('shopping-cart') ?>Iniciar compra</a>
    <p class="methods">Formas de pagamento:
      <span class="chip"><?= $icon('qr-code') ?>Pix</span>
      <span class="chip"><?= $icon('credit-card') ?>Cartão</span>
    </p>
  </div>
  <ol class="steps">
    <li class="step-card">
      <span class="step-icon"><?= $icon('scan-barcode') ?></span>
      <div><strong>Passe no leitor</strong><span class="muted">Aproxime o código de barras de cada produto.</span></div>
    </li>
    <li class="step-card">
      <span class="step-icon"><?= $icon('receipt-text') ?></span>
      <div><strong>Confira o total</strong><span class="muted">Veja os itens e altere as quantidades na tela.</span></div>
    </li>
    <li class="step-card">
      <span class="step-icon"><?= $icon('wallet') ?></span>
      <div><strong>Pague</strong><span class="muted">Escolha Pix ou cartão e finalize.</span></div>
    </li>
  </ol>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
