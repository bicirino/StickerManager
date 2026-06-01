<?php require __DIR__ . '/../../includes/header.php'; ?>
<h2 class="mb-3"><?= $fig['id'] ? 'Editar' : 'Nova' ?> Figurinha</h2>
<form method="post" action="index.php?r=figurinha_salvar" class="card card-body">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<input type="hidden" name="id" value="<?= $fig['id'] ?>">
<div class="row g-3">
  <div class="col-md-3"><label class="form-label">Número</label><input class="form-control" name="numero" value="<?= e($fig['numero']) ?>" required></div>
  <div class="col-md-9"><label class="form-label">Nome do jogador / item</label><input class="form-control" name="nome_jogador" value="<?= e($fig['nome_jogador']) ?>" required></div>
  <div class="col-md-3"><label class="form-label">Categoria</label><select name="categoria_id" class="form-select" required>
    <?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= $fig['categoria_id']==$c['id']?'selected':'' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-md-3"><label class="form-label">Seleção</label><select name="selecao_id" class="form-select"><option value="">—</option>
    <?php foreach ($selecoes as $s): ?><option value="<?= $s['id'] ?>" <?= $fig['selecao_id']==$s['id']?'selected':'' ?>><?= e($s['nome']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-md-3"><label class="form-label">Posição</label><select name="posicao_id" class="form-select"><option value="">—</option>
    <?php foreach ($posicoes as $p): ?><option value="<?= $p['id'] ?>" <?= $fig['posicao_id']==$p['id']?'selected':'' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="col-md-3"><label class="form-label">Raridade</label><select name="raridade" class="form-select">
    <?php foreach (['Comum','Especial','Lendária'] as $r): ?><option <?= $fig['raridade']===$r?'selected':'' ?>><?= $r ?></option><?php endforeach; ?>
  </select></div>
</div>
<div class="mt-3"><button class="btn btn-success">Salvar</button> <a href="index.php?r=figurinhas" class="btn btn-light">Cancelar</a></div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
