<?php require __DIR__ . '/../../includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow-sm"><div class="card-body p-4">
<h3 class="mb-3 text-center"><i class="bi bi-trophy-fill text-warning"></i> Entrar</h3>
<p class="text-muted small text-center">Use <code>admin@stickermanager.com</code> / <code>admin123</code> para teste</p>
<form method="post">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="mb-3"><label class="form-label">Senha</label><input class="form-control" type="password" name="senha" required></div>
  <button class="btn btn-success w-100">Entrar</button>
</form>
<hr><div class="text-center"><a href="index.php?r=registrar">Criar conta</a></div>
</div></div></div></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
