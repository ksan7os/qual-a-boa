<?php
// /api/skip.php
declare(strict_types=1);
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: ../auth/login.php?msg=" . urlencode("Faça login para continuar."));
  exit;
}

$pdo = pdo();
$idUsuario = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
$idLocal   = (int)($_POST['id_local'] ?? 0);

if ($idUsuario <= 0 || $idLocal <= 0) {
  header("Location: ../feed.php?msg=" . urlencode("Não foi possível pular este local."));
  exit;
}

// grava skip (ignora duplicados)
try {
  $ins = $pdo->prepare("INSERT INTO feed_feedback (id_usuario, id_local, acao) VALUES (?, ?, 'skip')");
  $ins->execute([$idUsuario, $idLocal]);
} catch (Throwable $e) {
  // silencia; não queremos travar a UX por isso
}

// guarda em sessão para o feed evitar repetir no mesmo request subsequente
$_SESSION['last_skip_local'] = $idLocal;

// volta para o feed com mensagem
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Location: ../feed.php?msg=" . urlencode("Ok, vamos te mostrar outras opções"));
exit;