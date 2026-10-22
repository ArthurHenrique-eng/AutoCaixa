<?php require __DIR__ . '/_header.php'; ?>
<section class="receipt" data-page="receipt">
  <h1>Compra concluída</h1>
  <p>Venda nº <?= $e($sale['id']) ?></p>
  <table class="receipt-table">
    <thead><tr><th>Produto</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= $e($item['product_name']) ?></td>
          <td><?= $e($item['quantity']) ?></td>
          <td><?= $e($money($item['unit_price_cents'])) ?></td>
          <td><?= $e($money($item['subtotal_cents'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="total-box"><span>Total pago</span><strong><?= $e($money($sale['total_cents'])) ?></strong></div>
  <a class="btn btn-primary btn-xl" href="/">Nova compra</a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
