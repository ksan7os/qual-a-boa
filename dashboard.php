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
    * {
      box-sizing: border-box;
      font-family: "Poppins", Arial, sans-serif;
    }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #3c0a5d 0%, #7e1c8d 60%, #a72791 100%);
    }
    .dash-card {
      width: 420px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      padding: 30px 34px 28px;
    }
    .dash-title {
      font-size: 2.1rem;
      font-weight: 600;
      color: #3c064c;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
    }
    .dash-subtitle {
      font-size: 0.92rem;
      color: #4b5563;
      margin-bottom: 20px;
    }
    .dash-actions {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .dash-link {
      display: block;
      text-align: center;
      width: 100%;
      height: 50px;
      line-height: 50px;
      border-radius: 999px;
      color: #fff;
      font-weight: 500;
      text-decoration: none;
      box-shadow: 0 5px 12px rgba(0,0,0,0.15);
      transition: transform .15s ease;
    }
    .dash-link:hover {
      transform: translateY(-2px);
    }
    .btn-perfil {
      background: linear-gradient(90deg, #3f0a84 0%, #6841b7 100%);
    }
    .btn-locais {
      background: #a72791;
    }
    .btn-painel {
      background: #8d30d2;
    }
    .btn-sair {
      background: #000;
      color: #fff;
    }

    @media (max-width: 460px) {
      .dash-card {
        width: 92%;
        padding: 26px 20px 24px;
      }
      .dash-title {
        font-size: 1.9rem;
      }
    }
  </style>
</head>
<body>
  <div class="dash-card">
    <h1 class="dash-title">Olá, <?= htmlspecialchars($nome) ?>!</h1>
    <p class="dash-subtitle">Qual a boa de hoje?</p>

    <div class="dash-actions">
      <a class="dash-link btn-perfil" href="<?= url('auth/perfil.php') ?>">Perfil</a>
      <a class="dash-link btn-locais" href="<?= url('locais/explorar.php') ?>">Locais</a>
      <a class="dash-link btn-painel" href="<?= url('locais/painel.php') ?>">Painel do administrador</a>
      <a class="dash-link btn-sair" href="<?= url('auth/logout.php') ?>">Sair</a>
    </div>
  </div>
</body>
</html>