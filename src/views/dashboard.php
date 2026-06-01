<?php require __DIR__ . '/../../includes/header.php'; ?>
<meta name="csrf" content="<?= csrf_token() ?>">
<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h2>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card text-bg-primary"><div class="card-body">
    <h6>Total</h6><h2><?= $stats['total'] ?></h2></div></div></div>
  <div class="col-md-3"><div class="card text-bg-success"><div class="card-body">
    <h6>Obtidas</h6><h2><?= $stats['obtidas'] ?></h2><small><?= $pct ?>%</small></div></div></div>
  <div class="col-md-3"><div class="card text-bg-secondary"><div class="card-body">
    <h6>Faltantes</h6><h2><?= $stats['faltantes'] ?></h2></div></div></div>
  <div class="col-md-3"><div class="card text-bg-warning"><div class="card-body">
    <h6>Repetidas (excedentes)</h6><h2><?= $stats['repetidas'] ?></h2></div></div></div>
</div>

<div class="card mb-4"><div class="card-body">
  <h5 class="card-title"><i class="bi bi-calculator"></i> Calculadora de Progresso</h5>
  <p class="mb-1">Progresso geral:</p>
  <div class="progress" style="height:26px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"><?= $pct ?>%</div></div>
  <?php $pacotes = (int)ceil($stats['faltantes']/5); ?>
  <p class="mt-3 mb-1">Faltam <strong><?= $stats['faltantes'] ?></strong> figurinhas. Estimativa: <strong>~<?= $pacotes ?></strong> pacotes (~R$ <?= number_format($pacotes*5,2,',','.') ?>) <small class="text-muted">*considerando 5 figurinhas/pacote a R$ 5,00</small></p>
  <?php if ($stats['repetidas']>0): ?>
  <div class="alert alert-warning mt-3 mb-0"><i class="bi bi-lightbulb-fill"></i> Você tem <strong><?= $stats['repetidas'] ?></strong> repetidas — troque antes de comprar mais pacotes!</div>
  <?php endif; ?>
</div></div>

<div class="card"><div class="card-body">
  <h5 class="card-title"><i class="bi bi-flag-fill"></i> Progresso por Seleção</h5>
  <div class="row g-2">
  <?php foreach ($porSel as $s): $p = $s['total']>0 ? round($s['obtidas']*100/$s['total'],0) : 0; ?>
    <div class="col-md-4 col-lg-3">
      <div class="border rounded p-2">
        <div class="d-flex justify-content-between"><strong><?= e($s['nome']) ?></strong><span class="badge bg-light text-dark"><?= $s['sigla'] ?></span></div>
        <div class="progress mt-1" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $p ?>%"></div></div>
        <small class="text-muted"><?= $s['obtidas'] ?>/<?= $s['total'] ?> (<?= $p ?>%)</small>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div></div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
