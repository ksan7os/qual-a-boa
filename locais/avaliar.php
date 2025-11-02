<?php
// locais/crud/avaliar.php — cria OU atualiza a avaliação (RF09 + RF10) e recalcula a média do local
declare(strict_types=1);

require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

session_start();

// Verifica se o usuário está logado
if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: ../../auth/login.php?msg=" . urlencode("Faça login para avaliar."));
  exit;
}

// Garante que a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Método inválido";
  exit;
}

$pdo = pdo();
$id_usuario = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$id_local   = isset($_POST['id_local']) ? (int) $_POST['id_local'] : 0;
$nota       = isset($_POST['nota']) ? (int) $_POST['nota'] : 0;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : null;

// validações básicas
if ($id_usuario <= 0 || $id_local <= 0) {
  header("Location: ../../detalhes.php?id={$id_local}&msg=" . urlencode("Dados inválidos."));
  exit;
}
if ($nota < 1 || $nota > 5) {
  header("Location: ../../detalhes.php?id={$id_local}&msg=" . urlencode("A nota deve estar entre 1 e 5."));
  exit;
}

// (Opcional) Exigir "Estou indo" antes de avaliar
/*
$chk = $pdo->prepare("SELECT 1 FROM estou_indo WHERE id_usuario = :u AND id_local = :l LIMIT 1");
$chk->execute([':u' => $id_usuario, ':l' => $id_local]);
if (!$chk->fetchColumn()) {
  header("Location: ../../detalhes.php?id={$id_local}&msg=" . urlencode("Você precisa marcar 'Estou indo' antes de avaliar."));
  exit;
}
*/

try {
  // RF10: Atualizar avaliação se já existir (UNIQUE KEY (id_usuario, id_local))
  // RF09: Inserir se ainda não houver avaliação
  $sql = "
    INSERT INTO avaliacoes (id_usuario, id_local, nota, comentario)
    VALUES (:u, :l, :n, :c)
    ON DUPLICATE KEY UPDATE
      nota = VALUES(nota),
      comentario = VALUES(comentario),
      atualizado_em = CURRENT_TIMESTAMP
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':u' => $id_usuario,
    ':l' => $id_local,
    ':n' => $nota,
    ':c' => $comentario !== '' ? $comentario : null,
  ]);

  // Recalcula a média do local após inserir/atualizar
  $upd = $pdo->prepare("
    UPDATE locais
       SET avaliacao_media = (
         SELECT COALESCE(AVG(nota),0)
           FROM avaliacoes
          WHERE id_local = :l
       )
     WHERE id_local = :l
  ");
  $upd->execute([':l' => $id_local]);

  // Redireciona corretamente para a página de detalhes
  header("Location: ../../qual-a-boa/locais/detalhes.php?id={$id_local}&msg=" . urlencode("Avaliação salva!"));
  exit;

} catch (PDOException $e) {
  // Registre o erro se desejar (error_log)
  // error_log('Erro ao salvar/atualizar avaliação: ' . $e->getMessage());
  header("Location: ../../qual-a-boa/locais/detalhes.php?id={$id_local}&msg=" . urlencode("Erro ao salvar avaliação."));
  exit;
}
?>