<?php
require_once("helpers.php");
require_admin();
$local = []; // formulário vazio
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Adicionar Local</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
  <div class="container-crud">
    <h2>Novo Local</h2>
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
