<?php
require_once("helpers.php");
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("ID inválido"); }
$id = (int)$_GET['id'];

$pdo = pdo_conn();
$stmt = $pdo->prepare("DELETE FROM locais WHERE id_local = ?");
$stmt->execute([$id]);

header("Location: listar.php?msg=" . urlencode("Local excluído!"));
exit;
