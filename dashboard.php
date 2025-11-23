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
  <meta charset="UTF-8">
  <title>Dashboard - Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
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

    .main-container p {
      font-size: 18px;
      color: #333;
      margin-bottom: 20px;
    }

    /* Alteração para os botões ficarem empilhados */
    .link-button {
      display: block;
      margin: 10px auto;
      padding: 12px 20px;
      border-radius: 30px;
      border: 2px solid #A63CE9;
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      background: #A63CE9;
      transition: background 0.3s, color 0.3s;
    }

    .link-button:hover {
      background: #8A2BE2;
      color: #fff;
    }

    /* Extra styling for the 'admin' button */
    .admin-button {
      background: #2E004F;
      color: #fff;
    }

    .admin-button:hover {
      background: #55007F;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <h1>Olá, <?=htmlspecialchars($nome)?> 👋</h1>
    <p>Qual a boa de hoje?</p>

    <!-- Botões -->
    <a class="link-button" href="<?= url('auth/perfil.php') ?>">Perfil</a>
    <a class="link-button" href="<?= url('locais/explorar.php') ?>">Explorar Locais</a>
    <a class="link-button" href="<?= url('feed.php') ?>">Feed de Recomendação</a>

    <!-- Verificando se é admin para exibir o painel -->
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
      <a class="link-button admin-button" href="<?= url('locais/crud/listar.php') ?>">Painel do Administrador</a>
    <?php endif; ?>

    <!-- Botão Sair -->
    <a class="link-button" href="<?= url('auth/logout.php') ?>">Sair</a>
  </div>
</body>
</html>