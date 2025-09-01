<?php
require __DIR__ . '/../conexao.php';
start_session();

if (is_logged_in()) {
    header('Location: ' . url('dashboard.php'));
    exit;
}

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['nome']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['senha']      ?? '';
    $pass2 = $_POST['confirmar']  ?? '';

    if ($name === '') $errors[] = 'Nome é obrigatório.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
    if (strlen($pass) < 6) $errors[] = 'A senha deve ter pelo menos 6 caracteres.';
    if ($pass !== $pass2) $errors[] = 'As senhas não conferem.';

    if (!$errors) {
        $pdo = pdo();

        $stmt = $pdo->prepare('SELECT 1 FROM usuario WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)');
            $ins->execute([$name, $email, $hash]);

            set_flash('ok', 'Conta criada com sucesso! Agora faça login.');
            header('Location: ' . url('auth/login.php'));
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Criar conta - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;padding:24px;max-width:560px;margin:auto}
    form{display:grid;gap:12px}
    input{padding:10px;border:1px solid #ccc;border-radius:8px;width:100%}
    button{padding:10px;border:0;border-radius:8px;background:#222;color:#fff;cursor:pointer}
    .err{background:#fee;border:1px solid #f99;padding:10px;border-radius:8px}
    .muted{color:#666}
    a{color:#06c}
  </style>
</head>
<body>
  <h1>Criar conta</h1>

  <?php if ($errors): ?>
    <div class="err">
      <ul><?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off" novalidate>
    <label>Nome
      <input type="text" name="nome" value="<?=htmlspecialchars($name)?>" required>
    </label>
    <label>E-mail
      <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required>
    </label>
    <label>Senha (mín. 6)
      <input type="password" name="senha" required>
    </label>
    <label>Confirmar senha
      <input type="password" name="confirmar" required>
    </label>
    <button type="submit">Criar conta</button>
  </form>

  <p class="muted">Já tem conta? <a href="<?= url('auth/login.php') ?>">Entrar</a></p>
</body>
</html>
