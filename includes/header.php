<?php
require_once __DIR__ . '/auth.php';
$u = current_user();
$f = flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>StickerManager — Copa 2026</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-trophy-fill"></i> StickerManager
      <span class="badge bg-warning text-dark ms-1">Copa 2026</span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <?php if ($u): ?>
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php?r=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?r=colecao"><i class="bi bi-grid-3x3-gap"></i> Minha Coleção</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?r=repetidas"><i class="bi bi-arrow-left-right"></i> Repetidas</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?r=relatorio"><i class="bi bi-file-earmark-pdf"></i> Relatório PDF</a></li>
        <?php if (!empty($u['is_admin'])): ?>
        <li class="nav-item"><a class="nav-link" href="index.php?r=figurinhas"><i class="bi bi-gear"></i> Gerenciar Figurinhas</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><span class="navbar-text me-3"><i class="bi bi-person-circle"></i> <?= e($u['nome']) ?></span></li>
        <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="index.php?r=logout">Sair</a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php if ($f): ?>
<div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show"><?= e($f['msg']) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
