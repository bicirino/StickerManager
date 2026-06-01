<?php require __DIR__ . '/../../includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow-sm"><div class="card-body p-4">
<h3 class="mb-3 text-center">Criar conta</h3>
<form method="post">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <div class="mb-3"><label class="form-label">Nome</label><input class="form-control" name="nome" required></div>
  <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
  <div class="mb-3"><label class="form-label">Senha (mín. 6)</label><input class="form-control" type="password" name="senha" required minlength="6"></div>
  <button class="btn btn-success w-100">Cadastrar</button>
</form>
<hr><div class="text-center"><a href="index.php?r=login">Já tenho conta</a></div>
</div></div></div></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
