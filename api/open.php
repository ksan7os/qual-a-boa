<?php
// /api/open.php
declare(strict_types=1);
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('is_logged_in') || !is_logged_in()) {
  http_response_code(204); // silencioso
  exit;
}

$pdo = pdo();
$idUsuario = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
$idLocal   = (int)($_GET['id_local'] ?? 0);
if ($idUsuario <= 0 || $idLocal <= 0) {
  http_response_code(204);
  exit;
}

$ins = $pdo->prepare("INSERT INTO feed_feedback (id_usuario, id_local, acao) VALUES (:u,:l,'open')");
$ins->execute([':u'=>$idUsuario, ':l'=>$idLocal]);

http_response_code(204);
