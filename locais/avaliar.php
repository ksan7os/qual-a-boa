<?php
// locais/avaliar.php — cria/atualiza avaliação e recalcula média
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (!is_logged_in()) {
  header("Location: ../login.php?msg=" . urlencode("Faça login para avaliar."));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die("Método inválido");
}

$pdo = pdo();
if (!$pdo) { die("Erro de conexão"); }

$id_usuario = (int) current_user_id();
$id_local   = isset($_POST['id_local']) && is_numeric($_POST['id_local']) ? (int)$_POST['id_local'] : 0;
$nota       = isset($_POST['nota']) ? (int)$_POST['nota'] : 0;
$comentario = trim($_POST['comentario'] ?? '');

if ($id_local <= 0 || $nota < 1 || $nota > 5) {
  header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Dados inválidos na avaliação."));
  exit;
}

try {
  // checa se o local existe
  $chk = $pdo->prepare("SELECT 1 FROM locais WHERE id_local = ?");
  $chk->execute([$id_local]);
  if (!$chk->fetchColumn()) {
    header("Location: explorar.php?msg=" . urlencode("Local não encontrado."));
    exit;
  }

  // insere ou atualiza a avaliação do usuário
  $sql = "INSERT INTO avaliacoes (id_usuario, id_local, nota, comentario)
          VALUES (:u, :l, :n, :c)
          ON DUPLICATE KEY UPDATE
            nota = VALUES(nota),
            comentario = VALUES(comentario),
            atualizado_em = CURRENT_TIMESTAMP";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':u' => $id_usuario,
    ':l' => $id_local,
    ':n' => $nota,
    ':c' => $comentario
  ]);

  // recalcula média do local
  $upd = $pdo->prepare("UPDATE locais
                        SET avaliacao_media = (SELECT COALESCE(AVG(nota),0) FROM avaliacoes WHERE id_local = :l)
                        WHERE id_local = :l");
  $upd->execute([':l' => $id_local]);

  header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Avaliação salva!"));
  exit;
} catch (PDOException $e) {
  header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Erro ao salvar avaliação."));
  exit;
}
