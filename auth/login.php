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

            // 🔹 Redirecionamento baseado no tipo de usuário
            if ($user['tipo_usuario'] === 'admin') {
                header('Location: ' . url('locais/crud.php')); // ajuste o caminho se necessário
            } else {
                header('Location: ' . url('dashboard.php'));
            }
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Entrar - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="main-container">
    <h1>Entrar</h1>

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

    <p class="text">Não tem conta? <a href="<?= url('auth/register.php') ?>">Criar conta</a></p>
    <p class="text">Esqueceu a senha? <a href="<?= url('auth/recuperacao-senha.php') ?>">Recuperar minha conta</a></p>
  </div>
</body>
</html>
