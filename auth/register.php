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
    * {
      box-sizing: border-box;
    }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #3c0a5d 0%, #7e1c8d 60%, #a72791 100%);
      font-family: "Poppins", Arial, sans-serif;
    }
    .register-wrapper {
      width: 440px;
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      padding: 38px 42px 32px;
      text-align: center;
    }
    .register-wrapper h1 {
      font-size: 2rem;
      font-weight: 600;
      color: #460a5f;
      margin-bottom: 20px;
    }
    .social-row {
      display: flex;
      gap: 14px;
      justify-content: center;
      margin-bottom: 26px;
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
      margin-bottom: 13px;
      text-align: left;
    }
    .form-control {
      width: 100%;
      height: 50px;
      border: none;
      outline: none;
      border-radius: 999px;
      background: #fff;
      padding: 0 18px;
      font-size: 0.9rem;
      box-shadow: 0 5px 12px rgba(0,0,0,0.15);
    }
    .form-helper {
      font-size: 0.75rem;
      color: #4b5563;
      margin-left: 6px;
      text-align: left;
      margin-bottom: 10px;
    }
    .btn-primary-auth {
      width: 140px;
      height: 42px;
      border: none;
      background: #2f0035;
      color: #fff;
      border-radius: 999px;
      cursor: pointer;
      font-weight: 500;
      margin: 6px auto 18px;
      display: block;
      transition: transform .15s ease;
    }
    .btn-primary-auth:hover {
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
    /* alerts do PHP */
    .alert {
      font-size: .78rem;
      padding: 7px 10px;
      border-radius: 8px;
      text-align: left;
      margin-bottom: 10px;
    }
    .alert-error {
      background: #ffe6e6;
      border: 1px solid #ff9f9f;
      color: #a10000;
    }
    @media (max-width: 480px) {
      .register-wrapper {
        width: 92%;
        padding: 32px 22px 28px;
      }
    }
  </style>
</head>
<body>
  <div class="register-wrapper">
    <h1>Cadastre-se</h1>

    <div class="social-row">
      <div class="social-btn">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="30" height="30" alt="Google">
      </div>
      <div class="social-btn">
        <img src="https://www.svgrepo.com/show/448224/facebook.svg" width="30" height="30" alt="Facebook">
      </div>
    </div>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
      <div class="form-group">
        <input
          id="nome"
          name="nome"
          class="form-control"
          type="text"
          placeholder="Nome Completo"
          value="<?= htmlspecialchars($name) ?>"
          required
        >
      </div>
      <div class="form-group">
        <input
          id="email"
          name="email"
          class="form-control"
          type="email"
          placeholder="Email"
          value="<?= htmlspecialchars($email) ?>"
          required
        >
      </div>
      <div class="form-group">
        <input
          id="senha"
          name="senha"
          class="form-control"
          type="password"
          placeholder="Insira sua senha"
          required
        >
      </div>
      <div class="form-group">
        <input
          id="confirmar"
          name="confirmar"
          class="form-control"
          type="password"
          placeholder="Confirmar senha"
          required
        >
      </div>
      <p class="form-helper">As senhas devem ser iguais</p>
      <button class="btn-primary-auth" type="submit">Entrar</button>
    </form>

    <p class="helper-text">
      Já tem uma conta?
      <a href="<?= url('auth/login.php') ?>">Entrar</a>
    </p>
  </div>
</body>
</html>