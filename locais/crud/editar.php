<?php
require_once("helpers.php");
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("ID inválido"); }
$id = (int)$_GET['id'];

$pdo = pdo_conn();
$stmt = $pdo->prepare("SELECT * FROM locais WHERE id_local = ?");
$stmt->execute([$id]);
$local = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$local) { die("Local não encontrado."); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Local #<?= (int)$local['id_local'] ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      background: linear-gradient(180deg, #4B0082, #B43BF0);
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: "Poppins", sans-serif;
      color: #333;
    }

    .container-crud {
      background: #fff;
      width: 80%;
      max-width: 960px;
      padding: 40px 50px;
      border-radius: 20px;
      box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
      margin-top: 50px;
      text-align: left;
    }

    h2 {
      font-size: 26px;
      color: #2E004F;
      margin-bottom: 20px;
    }

    .form-crud {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .row {
      display: flex;
      gap: 20px;
    }

    .half {
      width: 48%;
      padding: 12px 16px;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 12px;
    }

    .full {
      width: 100%;
      padding: 12px 16px;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 12px;
    }

    .btn {
      padding: 12px 20px;
      background: #A63CE9;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #8A2BE2;
    }

    .link-button {
      display: inline-block;
      background: transparent;
      color: #A63CE9;
      padding: 12px 20px;
      border-radius: 30px;
      font-size: 16px;
      text-decoration: none;
      margin-top: 20px;
      transition: background 0.3s, color 0.3s;
    }

    .link-button:hover {
      background: #A63CE9;
      color: #fff;
    }

    .success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      padding: 10px;
      border-radius: 8px;
      color: #065f46;
      font-size: 14px;
      margin-bottom: 14px;
    }
  </style>
</head>
<body>
  <div class="container-crud">
    <h2>Editar Local #<?= (int)$local['id_local'] ?></h2>
    <?php if ($msg = flash('msg')): ?>
      <div class="success"><?= $msg ?></div>
    <?php endif; ?>
    <form class="form-crud" action="salvar.php" method="POST" enctype="multipart/form-data">
      <?php include "_form_fields.php"; ?>
    </form>
    <p><a class="link-button" href="listar.php">← Voltar</a></p>
  </div>
</body>
</html>
