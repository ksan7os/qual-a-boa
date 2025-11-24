<?php
// usuario/historico.php — RF12: Histórico do Usuário
declare(strict_types=1);

require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: /auth/login.php?msg=" . urlencode("Faça login para ver seu histórico."));
  exit;
}

$pdo = pdo();

// Recupera o id do usuário logado (compatível com várias chaves de sessão)
$idUsuario = $_SESSION['id_usuario']
  ?? $_SESSION['user_id']
  ?? ($_SESSION['usuario']['id_usuario'] ?? null)
  ?? ($_SESSION['user']['id_usuario'] ?? null)
  ?? ($_SESSION['auth']['id_usuario'] ?? null);

$idUsuario = (int) ($idUsuario ?? 0);
if ($idUsuario <= 0) {
  header("Location: /auth/login.php?msg=" . urlencode("Sessão inválida."));
  exit;
}

// Mensagem opcional via GET
$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';

/* ============================================
 * NOVO 1/2: Expira automaticamente (>12h) os "Estou indo" ainda ativos
 * (garante consistência mesmo se o usuário nunca passou pelo perfil)
 * ============================================ */
$pdo->prepare("
  UPDATE estou_indo
     SET desmarcado_em = NOW(),
         desmarcado_motivo = 'auto'
   WHERE id_usuario = :u
     AND desmarcado_em IS NULL
     AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) > 12
")->execute([':u' => $idUsuario]);

// =========================
// Estou indo (estou_indo)
// =========================
// Seleciona SOMENTE os registros que foram CANCELADOS AUTOMATICAMENTE
// (não mostra cancelamentos manuais nem registros ainda ativos)
$sqlIr = "
  SELECT
    ei.data_marcacao,
    ei.desmarcado_em,
    ei.desmarcado_motivo,
    l.id_local,
    l.nome,
    l.tipo,
    l.faixa_preco,
    l.endereco,
    l.avaliacao_media,
    'encerrado (auto)' AS status
  FROM estou_indo ei
  JOIN locais l ON l.id_local = ei.id_local
  WHERE ei.id_usuario = :u
    AND ei.desmarcado_em IS NOT NULL
    AND ei.desmarcado_motivo = 'auto'
  ORDER BY ei.data_marcacao DESC
";
$stmtIr = $pdo->prepare($sqlIr);
$stmtIr->execute([':u' => $idUsuario]);
$indo = $stmtIr->fetchAll(PDO::FETCH_ASSOC);

// =========================
// Minhas avaliações
// =========================
$sqlAval = "
  SELECT a.id_avaliacao, a.nota, a.comentario, a.criado_em, a.atualizado_em,
         l.id_local, l.nome, l.tipo, l.faixa_preco, l.avaliacao_media
  FROM avaliacoes a
  JOIN locais l ON l.id_local = a.id_local
  WHERE a.id_usuario = :u
  ORDER BY a.atualizado_em DESC, a.criado_em DESC
";
$stmtAval = $pdo->prepare($sqlAval);
$stmtAval->execute([':u' => $idUsuario]);
$avaliacoes = $stmtAval->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <title>Meu histórico — Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- CSS base do projeto -->
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/explorar.css">
  <style>
    .section {
      width: 100%;
      max-width: 1000px;
      margin: 20px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      padding: 20px 22px;
    }
    .section h2 { margin-bottom: 10px; }
    .empty {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 10px;
      padding: 14px;
      color: #475569;
    }
    .items-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 14px;
      margin-top: 12px;
    }
    .item-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .meta { font-size: 13px; color: #64748b; }
    .title { font-weight: 600; color: #111827; }
    .rating { font-size: 14px; color: #334155; }
    .actions { margin-top: 6px; display: flex; gap: 8px; }
    .link-button { display: inline-block; padding: 8px 12px; border-radius: 8px; border: 1px solid #e5e7eb; }
    .main-container { max-width: 1020px; margin: 0 auto; padding: 14px; }
    .success { background:#ecfdf5; border:1px solid #a7f3d0; padding:10px; border-radius:8px; color:#065f46; }
  </style>
</head>
<body>

  <div class="main-container">
    <h1 style="margin-bottom:10px;">Meu histórico</h1>

    <?php if ($msg): ?>
      <div class="success" style="margin-bottom:14px;"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Estou indo (apenas auto-cancelados) -->
    <section class="section">
      <h2>Locais expirados automaticamente (últimas idas)</h2>
      <?php if (!$indo): ?>
        <div class="empty">Nenhum registro expirado automaticamente ainda.</div>
        <div style="margin-top:10px;">
          <a class="link-button" href="../locais/explorar.php">Explorar locais</a>
        </div>
      <?php else: ?>
        <div class="items-list">
          <?php foreach ($indo as $row): ?>
            <div class="item-card">
              <div class="title"><?php echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="meta">
                <?php
                  $fp = $row['faixa_preco'] ?? '';
                  $tipo = $row['tipo'] ?? '';
                  echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
                  echo $fp ? " • " . htmlspecialchars($fp, ENT_QUOTES, 'UTF-8') : "";
                ?>
              </div>
              <div class="meta">Endereço: <?php echo htmlspecialchars($row['endereco'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="meta">Marcado em: <?php echo htmlspecialchars($row['data_marcacao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="meta">Encerrado em: <?php echo htmlspecialchars($row['desmarcado_em'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="rating">
                Status: <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?> •
                Média do local: <?php echo number_format((float)($row['avaliacao_media'] ?? 0), 1, ',', '.'); ?> ★
              </div>
              <div class="actions">
                <a class="link-button" href="../locais/detalhes.php?id=<?php echo (int)$row['id_local']; ?>">Ver detalhes</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Minhas avaliações -->
    <section class="section">
      <h2>Minhas avaliações</h2>
      <?php if (!$avaliacoes): ?>
        <div class="empty">Você ainda não avaliou nenhum local.</div>
      <?php else: ?>
        <div class="items-list">
          <?php foreach ($avaliacoes as $row): ?>
            <div class="item-card">
              <div class="title"><?php echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="rating">Minha nota: <?php echo (int)$row['nota']; ?> / 5 ★</div>
              <?php if (!empty($row['comentario'])): ?>
                <div class="meta">Comentário: <?php echo nl2br(htmlspecialchars($row['comentario'], ENT_QUOTES, 'UTF-8')); ?></div>
              <?php endif; ?>
              <div class="meta">Atualizado em: <?php echo htmlspecialchars($row['atualizado_em'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="actions">
                <a class="link-button" href="../locais/detalhes.php?id=<?php echo (int)$row['id_local']; ?>">Ver/editar avaliação</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <div style="margin-top:10px;">
      <a class="link-button" href="../auth/perfil.php">Voltar</a>
    </div>
  </div>

</body>
</html>