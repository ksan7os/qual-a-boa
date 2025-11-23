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
  <meta charset="UTF-8">
  <title>Cadastre-se - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- css global do projeto -->
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      background: linear-gradient(180deg, #4B0082, #B43BF0);
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: "Poppins", sans-serif;
    }

    .main-container {
      background: #fff;
      width: 420px;
      padding: 40px 50px;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0px 4px 20px rgba(0,0,0,0.2);
    }

    .main-container h1 {
      font-size: 28px;
      color: #2E004F;
      margin-bottom: 20px;
    }

    .social-login {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 18px;
      margin-bottom: 25px;
    }

    .social-login img {
      width: 42px;
      cursor: pointer;
    }

    .form {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-bottom: 20px;
    }

    .form label {
      text-align: left;
      font-size: 14px;
      color: #333;
      font-weight: 500;
    }

    .form input {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #ddd;
      border-radius: 12px;
      font-size: 15px;
      outline: none;
      transition: 0.2s;
    }

    .form input:focus {
      border-color: #8A2BE2;
    }

    .form button {
      margin-top: 10px;
      background: #2E004F;
      color: #fff;
      padding: 12px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.2s;
    }

    .form button:hover {
      background: #55007F;
    }

    .muted {
      font-size: 14px;
      margin-top: 8px;
      color: #333;
    }

    .muted a {
      color: #8A2BE2;
      font-weight: 600;
      text-decoration: none;
    }

    .muted a:hover {
      text-decoration: underline;
    }

    .error {
      background: #ffd4d4;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 10px;
      color: #a30000;
      font-size: 14px;
    }
  </style>

</head>
<body>
  <div class="register-wrapper">
    <h1>Cadastre-se</h1>

    <!-- Exibindo erros -->
    <?php if ($errors): ?>
      <div class="error">
        <?php foreach ($errors as $e): ?><p><?=htmlspecialchars($e)?></p><?php endforeach; ?>
      </div>
      <div class="social-btn">
        <img src="https://www.svgrepo.com/show/448224/facebook.svg" width="30" height="30" alt="Facebook">
      </div>
    </div>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <!-- Formulário -->
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
  </div>
</body>
</html>