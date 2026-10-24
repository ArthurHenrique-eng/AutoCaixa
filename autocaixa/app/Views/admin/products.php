<?php require __DIR__ . '/_header.php';
use App\Core\Permissions;
$canManage = Permissions::allows($user['role'], 'products.manage');
$canStock = Permissions::allows($user['role'], 'stock.adjust');
?>
<div class="page-head">
  <div>
    <h1>Produtos</h1>
    <p class="muted">Cadastre produtos, altere preços e controle o estoque.</p>
  </div>
  <?php if ($canManage): ?>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" data-open-dialog="dlg-new">Novo produto</button>
  </div>
  <?php endif; ?>
</div>

<section class="card card-flush" aria-label="Lista de produtos">
  <?php if ($rows === []): ?>
    <div class="empty-state">
      <strong>Nenhum produto cadastrado</strong>
      <span>Cadastre o primeiro produto para que ele possa ser lido no totem.</span>
      <?php if ($canManage): ?><button type="button" class="btn btn-primary" data-open-dialog="dlg-new">Cadastrar primeiro produto</button><?php endif; ?>
    </div>
  <?php else: ?>
    <div class="toolbar">
      <label class="search">
        <span class="sr-only">Buscar produto por nome ou código</span>
        <?= $icon('search') ?>
        <input type="search" placeholder="Buscar por nome ou código" data-table-search="products-table" autocomplete="off">
      </label>
      <span class="muted" data-search-count="products-table"><?= $e(count($rows)) ?> <?= count($rows) === 1 ? 'produto' : 'produtos' ?></span>
    </div>
    <div class="table-wrap">
      <table class="table table-responsive" id="products-table">
        <thead><tr><th>Produto</th><th>Código</th><th class="num">Preço</th><th class="num">Em estoque</th><th class="num">Reservado</th><th class="num">Disponível</th><th class="num">Mínimo</th><th>Situação</th><th><span class="sr-only">Ações</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $p): $low = (int) $p['stock'] <= (int) $p['min_stock']; ?>
          <tr class="<?= (int) $p['active'] === 1 ? '' : 'inactive' ?>">
            <td data-label="Produto" class="row-title"><?= $e($p['name']) ?></td>
            <td data-label="Código"><?= $e($p['barcode']) ?></td>
            <td data-label="Preço" class="num"><?= $e($money((int) $p['price_cents'])) ?></td>
            <td data-label="Em estoque" class="num"><?= $e($p['stock']) ?></td>
            <td data-label="Reservado" class="num"><?= $e($p['reserved_stock']) ?></td>
            <td data-label="Disponível" class="num"><?= $e($p['available_stock']) ?></td>
            <td data-label="Mínimo" class="num"><?= $e($p['min_stock']) ?></td>
            <td data-label="Situação">
              <span class="badge-group">
                <?= (int) $p['active'] === 1 ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge">Inativo</span>' ?>
                <?php if ((int) $p['stock'] === 0): ?><span class="badge badge-danger">Sem estoque</span>
                <?php elseif ($low): ?><span class="badge badge-warning">Estoque baixo</span><?php endif; ?>
              </span>
            </td>
            <td data-label="">
              <div class="table-actions">
                <?php if ($canManage): ?><button type="button" class="btn btn-secondary btn-sm" data-open-dialog="dlg-edit-<?= $e($p['id']) ?>">Editar</button><?php endif; ?>
                <?php if ($canManage && $canStock): ?><button type="button" class="btn btn-secondary btn-sm" data-open-dialog="dlg-stock-<?= $e($p['id']) ?>">Estoque</button><?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="empty-state" data-search-empty="products-table" hidden>
        <strong>Nenhum produto encontrado</strong>
        <span>Tente buscar por outro nome ou código.</span>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php if ($canManage): ?>
<dialog class="modal" id="dlg-new" aria-labelledby="dlg-new-title">
  <form method="post" action="/admin/produtos" class="modal-form">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <div class="modal-head">
      <h2 id="dlg-new-title">Novo produto</h2>
      <button type="button" class="icon-btn" data-close-dialog aria-label="Fechar"><?= $icon('x') ?></button>
    </div>
    <label class="field"><span>Código de barras</span><input name="barcode" required inputmode="numeric" maxlength="64"><span class="help">Somente números, como na etiqueta.</span></label>
    <label class="field"><span>Nome</span><input name="name" required maxlength="160"></label>
    <div class="form-grid">
      <label class="field"><span>Preço (R$)</span><input name="price" required placeholder="18,90" maxlength="12" inputmode="decimal"></label>
      <label class="field"><span>Estoque mínimo</span><input name="min_stock" type="number" min="0" value="0"></label>
    </div>
    <label class="check"><input type="checkbox" name="active" checked> Disponível para venda</label>
    <p class="muted">O estoque começa em zero. Depois de cadastrar, use o botão "Estoque" para registrar a entrada.</p>
    <div class="modal-foot">
      <button type="button" class="btn btn-secondary" data-close-dialog>Cancelar</button>
      <button type="submit" class="btn btn-primary" data-loading-text="Cadastrando...">Cadastrar produto</button>
    </div>
  </form>
</dialog>

<?php foreach ($rows as $p): ?>
<dialog class="modal" id="dlg-edit-<?= $e($p['id']) ?>" aria-labelledby="dlg-edit-<?= $e($p['id']) ?>-title">
  <form method="post" action="/admin/produtos/<?= $e($p['id']) ?>" class="modal-form">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <div class="modal-head">
      <h2 id="dlg-edit-<?= $e($p['id']) ?>-title">Editar produto</h2>
      <button type="button" class="icon-btn" data-close-dialog aria-label="Fechar"><?= $icon('x') ?></button>
    </div>
    <label class="field"><span>Código de barras</span><input name="barcode" value="<?= $e($p['barcode']) ?>" required inputmode="numeric" maxlength="64"></label>
    <label class="field"><span>Nome</span><input name="name" value="<?= $e($p['name']) ?>" required maxlength="160"></label>
    <div class="form-grid">
      <label class="field"><span>Preço (R$)</span><input name="price" value="<?= $e(number_format($p['price_cents'] / 100, 2, ',', '')) ?>" required maxlength="12" inputmode="decimal"></label>
      <label class="field"><span>Estoque mínimo</span><input name="min_stock" type="number" min="0" value="<?= $e($p['min_stock']) ?>"></label>
    </div>
    <label class="check"><input type="checkbox" name="active" <?= (int) $p['active'] === 1 ? 'checked' : '' ?>> Disponível para venda</label>
    <div class="modal-foot">
      <button type="button" class="btn btn-secondary" data-close-dialog>Cancelar</button>
      <button type="submit" class="btn btn-primary" data-loading-text="Salvando...">Salvar alterações</button>
    </div>
  </form>
</dialog>
<?php if ($canStock): ?>
<dialog class="modal" id="dlg-stock-<?= $e($p['id']) ?>" aria-labelledby="dlg-stock-<?= $e($p['id']) ?>-title">
  <form method="post" action="/admin/produtos/<?= $e($p['id']) ?>/estoque" class="modal-form">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <div class="modal-head">
      <div>
        <h2 id="dlg-stock-<?= $e($p['id']) ?>-title">Ajustar estoque</h2>
        <p class="muted"><?= $e($p['name']) ?>: <?= $e($p['stock']) ?> em estoque, <?= $e($p['reserved_stock']) ?> reservado(s)</p>
      </div>
      <button type="button" class="icon-btn" data-close-dialog aria-label="Fechar"><?= $icon('x') ?></button>
    </div>
    <label class="field"><span>Motivo</span>
      <select name="reason">
        <option value="entrada">Entrada de mercadoria</option>
        <option value="ajuste">Ajuste de inventário</option>
        <option value="perda">Perda ou avaria</option>
      </select>
    </label>
    <label class="field"><span>Quantidade</span>
      <input name="delta" type="number" required>
      <span class="help">Entrada: número positivo. Perda: número negativo. No ajuste, use o sinal conforme a diferença. O estoque não pode ficar abaixo do que está reservado.</span>
    </label>
    <div class="modal-foot">
      <button type="button" class="btn btn-secondary" data-close-dialog>Cancelar</button>
      <button type="submit" class="btn btn-primary" data-loading-text="Registrando...">Registrar movimento</button>
    </div>
  </form>
</dialog>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
