<?php require __DIR__ . '/_header.php'; ?>
<section class="payment" data-page="payment" data-sale-id="<?= $e($saleId) ?>">
  <h1>Pagamento</h1>
  <div class="card status-card" id="status-card" data-state="waiting">
    <span class="status-icon" aria-hidden="true">
      <?= str_replace('class="icon"', 'class="icon i-wait"', $icon('loader-circle')) ?>
      <?= str_replace('class="icon"', 'class="icon i-ok"', $icon('circle-check')) ?>
      <?= str_replace('class="icon"', 'class="icon i-fail"', $icon('circle-alert')) ?>
    </span>
    <p class="big-status" id="status-text" aria-live="polite">Aguardando pagamento...</p>
    <p class="muted">Total: <strong><?= $e($money($sale['total_cents'])) ?></strong></p>
    <p class="muted small" id="status-hint">Esta tela atualiza sozinha assim que o pagamento for confirmado.</p>
    <p class="feedback" id="feedback" role="alert"></p>

    <?php if (($config['payment']['driver'] ?? '') === 'simulated'): ?>
      <button type="button" class="btn btn-secondary btn-lg" id="simulate-approval">
        <?= $icon('triangle-alert') ?>[SIMULAÇÃO] Aprovar pagamento
      </button>
    <?php endif; ?>
    <a class="btn btn-ghost btn-lg" href="/comprar" id="back-link"><?= $icon('arrow-left') ?>Voltar ao carrinho</a>
  </div>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
