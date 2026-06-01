<?php require __DIR__ . '/../../includes/header.php'; ?>
<meta name="csrf" content="<?= csrf_token() ?>">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> Minha Coleção</h2>
  <span class="text-muted small">Clique em uma figurinha para marcar como obtida (clique novamente p/ adicionar repetida)</span>
</div>

<form class="card card-body mb-3" method="get">
  <input type="hidden" name="r" value="colecao">
  <div class="row g-2">
    <div class="col-md-3"><input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar nº ou jogador..."></div>
    <div class="col-md-2"><select class="form-select" name="selecao"><option value="">Todas seleções</option>
      <?php foreach ($selecoes as $s): ?><option value="<?= $s['id'] ?>" <?= ($_GET['selecao'] ?? '')==$s['id']?'selected':'' ?>><?= e($s['nome']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><select class="form-select" name="posicao"><option value="">Toda posição</option>
      <?php foreach ($posicoes as $p): ?><option value="<?= $p['id'] ?>" <?= ($_GET['posicao'] ?? '')==$p['id']?'selected':'' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><select class="form-select" name="categoria"><option value="">Toda categoria</option>
      <?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['categoria'] ?? '')==$c['id']?'selected':'' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-2"><select class="form-select" name="status"><option value="">Todos status</option>
      <?php foreach (['obtidas'=>'Obtidas','faltantes'=>'Faltantes','repetidas'=>'Repetidas'] as $k=>$v): ?>
      <option value="<?= $k ?>" <?= ($_GET['status'] ?? '')===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-1 d-grid"><button class="btn btn-success"><i class="bi bi-funnel"></i></button></div>
  </div>
</form>

<p class="text-muted small"><?= count($figs) ?> figurinha(s) encontrada(s)</p>

<?php
$grupos = [];
foreach ($figs as $f) { $key = ($f['categoria_nome']) . ($f['selecao_nome'] ? ' — '.$f['selecao_nome'] : ''); $grupos[$key][] = $f; }
foreach ($grupos as $titulo => $items): ?>
<h5 class="mt-4"><span class="bg-selecao"><?= e($titulo) ?></span> <small class="text-muted">(<?= count($items) ?>)</small></h5>
<div class="row row-cols-3 row-cols-md-6 row-cols-lg-8 g-2 mb-3">
  <?php foreach ($items as $f): $q = (int)$f['quantidade']; ?>
  <div class="col">
    <div class="sticker-card <?= $q>0?'obtida':'' ?> <?= $q>1?'repetida':'' ?>" data-fig-id="<?= $f['id'] ?>">
      <div class="num">#<?= e($f['numero']) ?></div>
      <div class="name"><?= e($f['nome_jogador']) ?></div>
      <?php if ($q>1): ?><span class="badge bg-warning text-dark badge-rep">+<?= $q-1 ?></span><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
