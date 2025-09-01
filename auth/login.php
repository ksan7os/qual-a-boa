<?php
require __DIR__ . '/../conexao.php';
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
  <meta charset="utf-8">
  <title>Entrar - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;padding:24px;max-width:560px;margin:auto}
    form{display:grid;gap:12px}
    input{padding:10px;border:1px solid #ccc;border-radius:8px;width:100%}
    button{padding:10px;border:0;border-radius:8px;background:#222;color:#fff;cursor:pointer}
    .msg{background:#eef;border:1px solid #99f;padding:10px;border-radius:8px}
    .err{background:#fee;border:1px solid #f99;padding:10px;border-radius:8px}
    .muted{color:#666}
    a{color:#06c}
  </style>
</head>
<body>
  <h1>Entrar</h1>

  <?php if ($ok_msg): ?><div class="msg"><?=htmlspecialchars($ok_msg)?></div><?php endif; ?>
  <?php if ($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?>

  <form method="post" autocomplete="off">
    <label>E-mail
      <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required>
    </label>
    <label>Senha
      <input type="password" name="senha" required>
    </label>
    <button type="submit">Entrar</button>
  </form>

  <p class="muted">Não tem conta? <a href="<?= url('auth/register.php') ?>">Criar conta</a></p>
</body>
</html>
