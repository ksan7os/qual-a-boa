<?php
require __DIR__ . '/bd/conexao.php';
require_login();

$pdo = pdo();
$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT nome FROM usuario WHERE id_usuario = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch();
$nome = $row ? $row['nome'] : ($_SESSION['user_name'] ?? 'Usuário');
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">

</head>
<body>
  <div class="main-container">
    <h1>Olá, <?=htmlspecialchars($nome)?> 👋</h1>
    <p>Qual a boa de hoje? </p>
    <a class="link-button" href="<?= url('auth/perfil.php') ?>">Perfil</a>
    <a class="link-button" href="<?= url('locais/explorar.php') ?>">Explorar Locais</a>
    <a class="link-button" href="<?= url('feed.php') ?>">Feed de Recomendação</a>

    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

    <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
      <a class="link-button" href="<?= url('locais/crud/listar.php') ?>">Painel do Administrador</a>
    <?php endif; ?>

    <a class="link-button" href="<?= url('auth/logout.php') ?>">Sair</a>
  </div>
</body>
</html>
