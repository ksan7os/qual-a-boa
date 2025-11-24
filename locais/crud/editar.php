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
  <link rel="stylesheet" href="../../css/style.css">
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
