<?php
require __DIR__ . '/bd/conexao.php';
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
  <link rel="stylesheet" href="css/style.css">

</head>
<body>
  <div class="main-container">
    <h1>Qual a Boa?</h1>
    <p>Bem-vindo! Faça login ou crie sua conta para continuar.</p>
    <a class="link-button" href="<?= url('auth/login.php') ?>">Entrar</a>
    <a class="link-button" href="<?= url('auth/register.php') ?>">Criar conta</a>
  </div>
</body>
</html>
