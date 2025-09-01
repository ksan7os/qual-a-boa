<?php
require __DIR__ . '/conexao.php';
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
  <style>
    body{font-family:system-ui,Arial;padding:24px;max-width:760px;margin:auto}
    a.button{display:inline-block;padding:10px 14px;border:1px solid #ccc;border-radius:8px;text-decoration:none}
  </style>
</head>
<body>
  <h1>Olá, <?=htmlspecialchars($nome)?> 👋</h1>
  <p>Você está logado. A partir daqui vocês podem ligar o restante do MVP (CRUD de locais, filtros, etc.).</p>
  <p><a class="button" href="<?= url('logout.php') ?>">Sair</a></p>
</body>
</html>
