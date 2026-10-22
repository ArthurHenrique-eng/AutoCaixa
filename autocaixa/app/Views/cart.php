<?php require __DIR__ . '/_header.php'; ?>
<section class="cart" data-page="cart">
  <div class="scan-box">
    <label for="scanner">Passe o produto no leitor</label>
    <input id="scanner" type="text" inputmode="numeric" autocomplete="off" autofocus aria-describedby="feedback">
    <p id="feedback" class="feedback" role="alert" aria-live="assertive"></p>
  </div>

  <h2>Meu carrinho <span class="badge" id="item-count"><?= $e(count($summary['items'])) ?> itens</span></h2>
  <ul class="cart-list" id="cart-list">
    <?php foreach ($summary['items'] as $line): ?>
      <li class="cart-item" data-product-id="<?= $e($line['product_id']) ?>">
        <div class="item-info">
          <strong><?= $e($line['name']) ?></strong>
          <span class="muted">Código: <?= $e($line['barcode']) ?></span>
        </div>
        <div class="qty">
          <button type="button" class="btn btn-qty" data-action="dec" aria-label="Diminuir quantidade">−</button>
          <span class="qty-value"><?= $e($line['quantity']) ?></span>
          <button type="button" class="btn btn-qty" data-action="inc" aria-label="Aumentar quantidade">+</button>
        </div>
        <div class="item-price"><?= $e($money($line['subtotal_cents'])) ?></div>
        <button type="button" class="btn btn-remove" data-action="remove">Remover</button>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($summary['items'] === []): ?>
    <p class="empty">Nenhum produto no carrinho ainda.</p>
  <?php endif; ?>

  <div class="total-box">
    <span>Total da compra</span>
    <strong id="total"><?= $e($money($summary['total_cents'])) ?></strong>
  </div>

  <div class="pay-actions">
    <a class="btn btn-secondary btn-xl" href="/">Voltar</a>
    <button type="button" class="btn btn-primary btn-xl" data-pay="pix" <?= $summary['items'] === [] ? 'disabled' : '' ?>>Pagar com Pix</button>
    <button type="button" class="btn btn-primary btn-xl" data-pay="card" <?= $summary['items'] === [] ? 'disabled' : '' ?>>Pagar com cartão</button>
  </div>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
