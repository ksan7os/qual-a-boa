<?php
require __DIR__ . '/../bd/conexao.php';
start_session();

if (is_logged_in()) {
    header('Location: ' . url('dashboard.php'));
    exit;
}

$ok_msg = get_flash('ok');
$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['senha'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail inválido.';
    } elseif ($pass === '') {
        $error = 'Senha é obrigatória.';
    } else {
        $pdo = pdo();
        $stmt = $pdo->prepare('SELECT id_usuario, nome, email, senha, tipo_usuario FROM usuario WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pass, $user['senha'])) {
            $error = 'E-mail ou senha incorretos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int)$user['id_usuario'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_role'] = $user['tipo_usuario'];

            header('Location: ' . url('dashboard.php'));
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- seu css global -->
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* Fundo */
    body {
      margin: 0;
      background: linear-gradient(180deg, #4B0082, #B43BF0);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: "Poppins", sans-serif;
    }

    /* Container branco central */
    .main-container {
      background: #fff;
      width: 420px;
      padding: 40px 50px;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0px 4px 20px rgba(0,0,0,0.2);
    }

    /* Título */
    .main-container h1 {
      font-size: 28px;
      color: #2E004F;
      margin-bottom: 20px;
    }

    /* Ícones de login social */
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

    /* Formulário */
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

    /* Botão */
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

    /* Textos inferiores */
    .text {
      font-size: 14px;
      margin-top: 8px;
      color: #333;
    }

    .text a {
      color: #8A2BE2;
      font-weight: 600;
      text-decoration: none;
    }

    .text a:hover {
      text-decoration: underline;
    }

    /* Mensagens de erro/sucesso */
    .success {
      background: #d4ffd8;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 10px;
      color: #1d7a2d;
      font-size: 14px;
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
  <div class="main-container">
    <h1>Login</h1>

    <div class="social-login">
        <img src="../img/google.png" alt="Google">
        <img src="../img/facebook.png" alt="Facebook">
    </div>

    <?php if ($ok_msg): ?><div class="success"><?=htmlspecialchars($ok_msg)?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <form method="post" autocomplete="off" class="form">
      <label>E-mail
        <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required>
      </label>
      <label>Senha
        <input type="password" name="senha" required>
      </label>
      <button type="submit">Entrar</button>
    </form>

    <p class="helper-text" style="margin-top: 0;">
      Não tem uma conta?
      <a href="<?= url('auth/register.php') ?>">Cadastre-se</a>
    </p>
    <p class="helper-text" style="margin-top: 4px;">
      <a href="<?= url('auth/recuperacao-senha.php') ?>">Esqueceu a senha?</a>
    </p>
  </div>
</body>
</html>