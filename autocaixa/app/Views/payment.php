<?php require __DIR__ . '/_header.php'; ?>
<section class="payment" data-page="payment" data-sale-id="<?= $e($saleId) ?>">
  <h1>Pagamento</h1>
  <p class="big-status" id="status-text" aria-live="polite">Aguardando pagamento...</p>
  <p class="muted">Total: <?= $e($money($sale['total_cents'])) ?></p>
  <p class="feedback" id="feedback" role="alert"></p>

  <?php if (($config['payment']['driver'] ?? '') === 'simulated'): ?>
    <button type="button" class="btn btn-secondary btn-xl" id="simulate-approval">
      [SIMULAÇÃO] Aprovar pagamento
    </button>
  <?php endif; ?>
  <a class="btn btn-secondary" href="/comprar" id="back-link">Voltar ao carrinho</a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
