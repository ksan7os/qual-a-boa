<?php
require __DIR__ . '/conexao.php';
start_session();

if (is_logged_in()) {
    header('Location: ' . url('dashboard.php'));
    exit;
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Qual a Boa? - Início</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;padding:24px;max-width:560px;margin:auto}
    a.button{display:inline-block;padding:10px 14px;border:1px solid #ccc;border-radius:8px;text-decoration:none}
    .stack{display:flex;gap:12px}
  </style>
</head>
<body>
  <h1>Qual a Boa?</h1>
  <p>Bem-vindo! Faça login ou crie sua conta para continuar.</p>
  <div class="stack">
    <a class="button" href="<?= url('auth/login.php') ?>">Entrar</a>
    <a class="button" href="<?= url('auth/register.php') ?>">Criar conta</a>
  </div>
</body>
</html>
