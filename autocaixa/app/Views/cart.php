<?php require __DIR__ . '/_header.php';
$count = array_sum(array_column($summary['items'], 'quantity'));
$empty = $summary['items'] === [];
?>
<section class="checkout" data-page="cart">
  <div class="checkout-main">
    <div class="scan-card">
      <span class="scan-icon"><?= $icon('scan-barcode') ?></span>
      <div class="scan-field">
        <label for="scanner">Passe o produto no leitor</label>
        <input id="scanner" type="text" inputmode="numeric" autocomplete="off" autofocus
               placeholder="Código de barras" aria-describedby="feedback">
        <p id="feedback" class="feedback" role="alert" aria-live="assertive"></p>
      </div>
    </div>

    <div class="section-head">
      <h2>Meu carrinho</h2>
      <span class="badge badge-primary" id="item-count"><?= $e($count) ?> <?= $count === 1 ? 'item' : 'itens' ?></span>
    </div>
    <ul class="cart-list" id="cart-list">
      <?php foreach ($summary['items'] as $line): ?>
        <li class="cart-item" data-product-id="<?= $e($line['product_id']) ?>">
          <div class="item-info">
            <strong><?= $e($line['name']) ?></strong>
            <span class="muted">Código: <?= $e($line['barcode']) ?></span>
          </div>
          <div class="qty">
            <button type="button" class="btn btn-qty" data-action="dec" aria-label="Diminuir quantidade"><?= $icon('minus') ?></button>
            <span class="qty-value"><?= $e($line['quantity']) ?></span>
            <button type="button" class="btn btn-qty" data-action="inc" aria-label="Aumentar quantidade"><?= $icon('plus') ?></button>
          </div>
          <div class="item-price">
            <strong><?= $e($money($line['subtotal_cents'])) ?></strong>
            <span class="muted"><?= $e($money($line['unit_price_cents'])) ?> cada</span>
          </div>
          <button type="button" class="btn btn-remove" data-action="remove"><?= $icon('trash-2') ?>Remover</button>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="card empty-state empty" <?= $empty ? '' : 'hidden' ?>>
      <span class="empty-icon"><?= $icon('shopping-cart') ?></span>
      <strong>Seu carrinho está vazio</strong>
      <span>Passe o primeiro produto no leitor para começar.</span>
    </div>
  </div>

  <aside class="card summary-card" aria-label="Resumo e pagamento">
    <h2>Resumo</h2>
    <div class="total-box">
      <span>Total da compra</span>
      <strong id="total"><?= $e($money($summary['total_cents'])) ?></strong>
    </div>
    <div class="pay-actions">
      <button type="button" class="btn btn-primary btn-xl" data-pay="pix" <?= $empty ? 'disabled' : '' ?>><?= $icon('qr-code') ?>Pagar com Pix</button>
      <button type="button" class="btn btn-primary btn-xl" data-pay="card" <?= $empty ? 'disabled' : '' ?>><?= $icon('credit-card') ?>Pagar com cartão</button>
      <a class="btn btn-ghost btn-lg" href="/"><?= $icon('arrow-left') ?>Voltar</a>
    </div>
  </aside>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
