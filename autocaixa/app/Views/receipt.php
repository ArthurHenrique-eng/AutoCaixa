<?php require __DIR__ . '/_header.php'; ?>
<section class="receipt" data-page="receipt">
  <div class="success-head">
    <span class="status-icon" aria-hidden="true"><?= $icon('circle-check') ?></span>
    <h1>Compra concluída</h1>
    <p class="muted">Venda nº <?= $e($sale['id']) ?></p>
  </div>
  <div class="card card-flush">
    <div class="table-wrap">
      <table class="table table-responsive">
        <thead><tr><th>Produto</th><th class="num">Qtd</th><th class="num">Preço</th><th class="num">Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td data-label="Produto" class="row-title"><?= $e($item['product_name']) ?></td>
              <td data-label="Qtd" class="num"><?= $e($item['quantity']) ?></td>
              <td data-label="Preço" class="num"><?= $e($money($item['unit_price_cents'])) ?></td>
              <td data-label="Subtotal" class="num"><?= $e($money($item['subtotal_cents'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="total-box"><span>Total pago</span><strong><?= $e($money($sale['total_cents'])) ?></strong></div>
  <a class="btn btn-primary btn-xl btn-block" href="/"><?= $icon('shopping-cart') ?>Nova compra</a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
