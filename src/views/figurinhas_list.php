<?php require __DIR__ . '/../../includes/header.php'; ?>
<div class="d-flex justify-content-between mb-3">
  <h2><i class="bi bi-gear"></i> Gerenciar Figurinhas</h2>
  <a class="btn btn-success" href="index.php?r=figurinha_form"><i class="bi bi-plus-circle"></i> Nova</a>
</div>
<div class="table-responsive"><table class="table table-sm table-striped">
<thead><tr><th>Nº</th><th>Jogador</th><th>Categoria</th><th>Seleção</th><th>Posição</th><th>Raridade</th><th></th></tr></thead>
<tbody>
<?php foreach ($figs as $f): ?>
<tr>
  <td><?= e($f['numero']) ?></td>
  <td><?= e($f['nome_jogador']) ?></td>
  <td><?= e($f['categoria_nome']) ?></td>
  <td><?= e($f['selecao_nome'] ?? '-') ?></td>
  <td><?= e($f['posicao_nome'] ?? '-') ?></td>
  <td><?= e($f['raridade']) ?></td>
  <td class="text-end">
    <a class="btn btn-sm btn-outline-primary" href="index.php?r=figurinha_form&id=<?= $f['id'] ?>"><i class="bi bi-pencil"></i></a>
    <form method="post" action="index.php?r=figurinha_excluir" class="d-inline" onsubmit="return confirm('Excluir?')">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $f['id'] ?>">
      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
