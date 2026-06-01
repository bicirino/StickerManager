<?php require __DIR__ . '/../../includes/header.php'; ?>
<meta name="csrf" content="<?= csrf_token() ?>">
<h2 class="mb-3"><i class="bi bi-arrow-left-right"></i> Repetidas — pronto para a banquinha</h2>
<p class="text-muted">Mostre essa lista nos pontos de troca. Use os botões + / − para ajustar a quantidade conforme você troca.</p>

<?php if (empty($figs)): ?>
<div class="alert alert-info">Você não tem nenhuma figurinha repetida no momento.</div>
<?php else: ?>
<div class="table-responsive"><table class="table table-striped align-middle">
<thead class="table-success"><tr><th>Nº</th><th>Jogador</th><th>Seleção</th><th>Posição</th><th class="text-center">Excedentes</th><th class="text-center" style="width:160px">Ajustar</th></tr></thead>
<tbody>
<?php foreach ($figs as $f): ?>
<tr>
  <td><strong>#<?= e($f['numero']) ?></strong></td>
  <td><?= e($f['nome_jogador']) ?></td>
  <td><span class="badge bg-success"><?= e($f['sigla'] ?? '-') ?></span> <?= e($f['selecao_nome'] ?? '-') ?></td>
  <td><?= e($f['posicao_nome'] ?? '-') ?></td>
  <td class="text-center"><span class="badge bg-warning text-dark fs-6">+<?= $f['quantidade']-1 ?></span></td>
  <td class="text-center">
    <button class="btn btn-sm btn-outline-danger rep-btn" data-fig-id="<?= $f['id'] ?>" data-delta="-1">−</button>
    <button class="btn btn-sm btn-outline-success rep-btn" data-fig-id="<?= $f['id'] ?>" data-delta="1">+</button>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
