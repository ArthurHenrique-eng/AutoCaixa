<?php require __DIR__ . '/_header.php'; ?>
<h1>Produtos</h1>

<?php if (\App\Core\Permissions::allows($user['role'], 'products.manage')): ?>
<details class="panel">
  <summary>Cadastrar novo produto</summary>
  <form method="post" action="/admin/produtos" class="form-grid">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label>Código de barras <input name="barcode" required inputmode="numeric" maxlength="64"></label>
    <label>Nome <input name="name" required maxlength="160"></label>
    <label>Preço (R$) <input name="price" required placeholder="18,90" maxlength="12"></label>
    <label>Estoque mínimo <input name="min_stock" type="number" min="0" value="0"></label>
    <label class="check"><input type="checkbox" name="active" checked> Ativo</label>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>
</details>
<?php endif; ?>

<table class="table">
  <thead><tr><th>Produto</th><th>Código</th><th>Preço</th><th>Estoque</th><th>Mínimo</th><th>Situação</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $p): ?>
    <tr class="<?= (int) $p['active'] === 1 ? '' : 'inactive' ?>">
      <td><?= $e($p['name']) ?></td>
      <td><?= $e($p['barcode']) ?></td>
      <td><?= $e($money((int) $p['price_cents'])) ?></td>
      <td><?= $e($p['stock']) ?></td>
      <td><?= $e($p['min_stock']) ?></td>
      <td><?= (int) $p['active'] === 1 ? 'Ativo' : 'Inativo' ?></td>
      <td>
        <?php if (\App\Core\Permissions::allows($user['role'], 'products.manage')): ?>
        <details>
          <summary>Editar</summary>
          <form method="post" action="/admin/produtos/<?= $e($p['id']) ?>" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <label>Código <input name="barcode" value="<?= $e($p['barcode']) ?>" required maxlength="64"></label>
            <label>Nome <input name="name" value="<?= $e($p['name']) ?>" required maxlength="160"></label>
            <label>Preço <input name="price" value="<?= $e(number_format($p['price_cents'] / 100, 2, ',', '')) ?>" required maxlength="12"></label>
            <label>Mínimo <input name="min_stock" type="number" min="0" value="<?= $e($p['min_stock']) ?>"></label>
            <label class="check"><input type="checkbox" name="active" <?= (int) $p['active'] === 1 ? 'checked' : '' ?>> Ativo</label>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </form>
        </details>
        <?php if (\App\Core\Permissions::allows($user['role'], 'stock.adjust')): ?>
        <details>
          <summary>Ajustar estoque</summary>
          <form method="post" action="/admin/produtos/<?= $e($p['id']) ?>/estoque" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <label>Quantidade (use − para saída) <input name="delta" type="number" required></label>
            <label>Motivo
              <select name="reason">
                <option value="entrada">Entrada de mercadoria</option>
                <option value="ajuste">Ajuste de inventário</option>
                <option value="perda">Perda ou avaria</option>
              </select>
            </label>
            <button type="submit" class="btn btn-secondary">Registrar</button>
          </form>
        </details>
        <?php endif; ?>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_footer.php'; ?>
