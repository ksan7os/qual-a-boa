<?php
require __DIR__ . '/../bd/conexao.php';
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
  <link rel="stylesheet" href="../css/style.css">

</head>
<body>
  <div class="main-container">
    <h1>Criar conta</h1>

    <?php if ($errors): ?>
      <div class="error">
        <?php foreach ($errors as $e): ?><p><?=htmlspecialchars($e)?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate class="form">
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
  <div>
</body>
</html>
