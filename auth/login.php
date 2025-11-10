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
    /* ===== layout da tela de login ===== */
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #3c0a5d 0%, #7e1c8d 60%, #a72791 100%);
      font-family: "Poppins", Arial, sans-serif;
    }

    .login-wrapper {
      width: 440px;
      background: #fff;
      border-radius: 24px;
      padding: 38px 42px 32px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      text-align: center;
    }

    .login-wrapper h1 {
      font-size: 2rem;
      color: #460a5f;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .social-row {
      display: flex;
      gap: 14px;
      justify-content: center;
      margin-bottom: 24px;
    }

    .social-btn {
      width: 52px;
      height: 52px;
      background: #fff;
      border-radius: 50%;
      display: grid;
      place-items: center;
      box-shadow: 0 6px 14px rgba(0,0,0,0.18);
    }

    .form-group {
      margin-bottom: 14px;
      text-align: left;
    }

    .form-group label {
      font-size: 0.8rem;
      color: #1f2937;
      margin-left: 8px;
      margin-bottom: 4px;
      display: block;
    }

    .form-control {
      width: 100%;
      height: 50px;
      border: none;
      outline: none;
      background: #fff;
      border-radius: 999px;
      padding: 0 18px;
      font-size: 0.9rem;
      box-shadow: 0 5px 12px rgba(0,0,0,0.15);
    }

    .btn-primary-login {
      width: 140px;
      height: 42px;
      border: none;
      outline: none;
      background: #2f0035;
      color: #fff;
      border-radius: 999px;
      cursor: pointer;
      font-weight: 500;
      margin: 10px auto 18px;
      display: block;
      transition: transform 0.15s ease;
    }

    .btn-primary-login:hover {
      transform: translateY(-2px);
    }

    .helper-text {
      font-size: 0.8rem;
      color: #4b5563;
    }

    .helper-text a {
      color: #a72791;
      font-weight: 500;
      text-decoration: none;
    }

    .helper-text a:hover {
      text-decoration: underline;
    }

    /* alerts vindos do PHP */
    .alert {
      font-size: 0.78rem;
      padding: 7px 10px;
      border-radius: 8px;
      text-align: left;
      margin-bottom: 10px;
    }
    .alert-ok {
      background: #e8fff0;
      border: 1px solid #8ee0b0;
      color: #06663a;
    }
    .alert-error {
      background: #ffe6e6;
      border: 1px solid #ff9f9f;
      color: #a10000;
    }

    @media (max-width: 480px) {
      .login-wrapper {
        width: 90%;
        padding: 32px 24px 28px;
      }
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <h1>Login</h1>

    <div class="social-row">
      <!-- Mantive suas imagens externas, só coloquei dentro do círculo -->
      <div class="social-btn">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="30" height="30" alt="Google">
      </div>
      <div class="social-btn">
        <img src="https://www.svgrepo.com/show/448224/facebook.svg" width="30" height="30" alt="Facebook">
      </div>
    </div>

    <?php if ($ok_msg): ?>
      <div class="alert alert-ok"><?= htmlspecialchars($ok_msg) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          class="form-control"
          type="email"
          name="email"
          value="<?= htmlspecialchars($email) ?>"
          required
        >
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input
          id="senha"
          class="form-control"
          type="password"
          name="senha"
          required
        >
      </div>

      <button type="submit" class="btn-primary-login">Entrar</button>
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