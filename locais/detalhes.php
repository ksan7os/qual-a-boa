<?php
// locais/detalhes.php — RF07 (detalhes) + RF08 (estou indo) + RF09 (avaliações)
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = pdo();
if (!$pdo) { die("Erro de conexão com o banco de dados."); }

// 1) validação do id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<h3>Local inválido.</h3>";
  exit;
}
$id_local = (int)$_GET['id'];

// 2) busca do local
try {
  $stmt = $pdo->prepare("SELECT * FROM locais WHERE id_local = :id");
  $stmt->bindValue(':id', $id_local, PDO::PARAM_INT);
  $stmt->execute();
  $local = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$local) { echo "<h3>Local não encontrado.</h3>"; exit; }
} catch (PDOException $e) {
  echo "<h3>Erro ao carregar informações do local.</h3>";
  exit;
}

// 3) dados para exibição
$nome        = htmlspecialchars($local['nome'] ?? '');
$tipo        = htmlspecialchars($local['tipo'] ?? '');
$descricao   = nl2br(htmlspecialchars($local['descricao'] ?? ''));
$endereco    = htmlspecialchars($local['endereco'] ?? '');
$faixa       = htmlspecialchars($local['faixa_preco'] ?? '');
$horario     = nl2br(htmlspecialchars($local['horario_funcionamento'] ?? ''));
$site        = trim($local['site'] ?? '');
$telefone    = htmlspecialchars($local['telefone'] ?? '');
$email       = htmlspecialchars($local['email_contato'] ?? '');
$redes_raw   = trim($local['redes_sociais'] ?? '');
$servicos_raw= trim($local['servicos'] ?? '');
$avaliacao   = number_format((float)($local['avaliacao_media'] ?? 0), 1);
$imagem_capa = "../img/capa-locais/" . htmlspecialchars($local['imagem_capa'] ?: 'default-profile.jpg');

// helpers
function explode_list($str) {
  if (!$str) return [];
  $parts = array_map('trim', explode(',', $str));
  return array_values(array_filter($parts, fn($v) => $v !== ''));
}

// serviços como chips
$servicos = array_map('htmlspecialchars', explode_list($servicos_raw));

// redes sociais clicáveis
$redes = explode_list($redes_raw);
$redes_links = array_map(function($link) {
  $link = trim($link);
  $label = htmlspecialchars($link);
  if (!preg_match('~^https?://~i', $link)) { $link = 'https://' . $link; }
  $href = htmlspecialchars($link);
  return "<a href=\"$href\" target=\"_blank\" rel=\"noopener noreferrer\">$label</a>";
}, $redes);

// site clicável
$site_link = '';
if ($site !== '') {
  $href = htmlspecialchars((preg_match('~^https?://~i', $site) ? $site : "https://$site"));
  $site_link = "<a href=\"$href\" target=\"_blank\" rel=\"noopener noreferrer\">$href</a>";
}

/* ========================== RF09: avaliações ========================== */
$id_usuario_logado = current_user_id(); // vem do bd/auth.php

// >>> NOVO: expira automaticamente qualquer "Estou indo" ativo > 12h (soft delete), apenas se logado
if ($id_usuario_logado) {
  $expira = $pdo->prepare("
    UPDATE estou_indo
       SET desmarcado_em = NOW(),
           desmarcado_motivo = 'auto'
     WHERE id_usuario = :u
       AND desmarcado_em IS NULL
       AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) > 12
  ");
  $expira->execute([':u' => $id_usuario_logado]);
}

// RF08 — verificar se o usuário já marcou esse local (ATIVO: sem desmarcar e <= 12h)
$ja_indo = false;
if ($id_usuario_logado) {
  $st = $pdo->prepare("
    SELECT 1
      FROM estou_indo 
     WHERE id_usuario = :u 
       AND id_local = :l 
       AND desmarcado_em IS NULL
       AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) <= 12
     LIMIT 1
  ");
  $st->execute([':u' => $id_usuario_logado, ':l' => $id_local]);
  $ja_indo = $st->fetchColumn() > 0;
}

// minha avaliação (para pré-preencher)
$minha_avaliacao = null;
if ($id_usuario_logado) {
  $st = $pdo->prepare("SELECT nota, comentario FROM avaliacoes WHERE id_local = ? AND id_usuario = ?");
  $st->execute([$id_local, $id_usuario_logado]); // <<< usa o mesmo id do usuário logado
  $minha_avaliacao = $st->fetch(PDO::FETCH_ASSOC);
}

// últimas avaliações (com nome do usuário)
$st2 = $pdo->prepare("
  SELECT a.nota, a.comentario, a.criado_em, u.nome
  FROM avaliacoes a
  JOIN usuario u ON u.id_usuario = a.id_usuario
  WHERE a.id_local = ?
  ORDER BY a.criado_em DESC
");
$st2->execute([$id_local]);
$avaliacoes = $st2->fetchAll(PDO::FETCH_ASSOC);
/* ===================================================================== */

/* ========================== RF08: Estou indo ========================== */
/* >>> Ajuste: usar o mesmo critério de ATIVO do $ja_indo para o botão  */
$jaMarcou = $ja_indo;
/* ===================================================================== */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($nome) ?> — Detalhes do Local</title>

  <!-- CSS moderno do seu amigo (com pequenas adaptações) -->
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont,
        "Segoe UI", sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: radial-gradient(circle at bottom, #9c1fd4 0%, #000 60%);
      padding: 40px 0;
    }

    .card {
      width: 1000px;
      max-width: 95vw;
      background: #ffffff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 18px 35px rgba(0, 0, 0, 0.35);
    }

    .card-map {
      width: 100%;
      height: 320px;
      background-size: cover;
      background-position: center;
    }

    .card-content {
      padding: 26px 40px 32px;
      position: relative;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      gap: 24px;
    }

    .title-block {
      flex: 1;
    }

    .title-block h1 {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .tags-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .tag-pill {
      padding: 8px 16px;
      border-radius: 999px;
      background: #d3ddff;
      color: #333;
      font-size: 14px;
      font-weight: 500;
    }

    .info-block {
      width: 260px;
      font-size: 13px;
      color: #555;
      line-height: 1.5;
    }

    .info-block strong {
      color: #222;
    }

    .section-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 12px;
    }

    .about-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 32px;
      gap: 30px;
    }

    .about-text {
      flex: 1;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }

    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .chip {
      padding: 6px 14px;
      background: #eef4ff;
      border: 1px solid #cddcff;
      color: #1d4ed8;
      border-radius: 999px;
      font-size: 13px;
    }

    .rating-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 30px 0;
    }

    .stars {
      display: flex;
      gap: 4px;
      color: #f4c43b;
      font-size: 22px;
    }

    .rating-value {
      font-size: 17px;
      font-weight: 600;
      color: #444;
    }

    .reserve-btn {
      display: block;
      margin: 0 auto;
      width: 60%;
      max-width: 420px;
      padding: 14px 0;
      border-radius: 999px;
      border: none;
      background: #7062ff;
      color: #ffffff;
      font-size: 18px;
      font-weight: 600;
      letter-spacing: 0.03em;
      cursor: pointer;
      box-shadow: 0 12px 18px rgba(55, 40, 201, 0.5);
      transition: 0.15s ease;
      text-align: center;
      text-decoration: none;
    }

    .reserve-btn:hover {
      background: #5e4cff;
      transform: translateY(-1px);
      box-shadow: 0 15px 25px rgba(55, 40, 201, 0.7);
    }

    /* Avaliações */
    .av-card {
      margin-top: 35px;
      padding: 18px;
      background: #f8fafc;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
    }

    .rate-row {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-bottom: 14px;
    }

    .stars-input {
      display: flex;
      flex-direction: row-reverse;
      gap: 6px;
    }

    .stars-input input { display: none; }
    .stars-input label {
      font-size: 24px;
      cursor: pointer;
      filter: grayscale(25%);
      transition: 0.1s ease;
    }
    .stars-input input:checked ~ label { filter: grayscale(0); }
    .stars-input label:hover { transform: scale(1.05); }

    textarea.av-text {
      width: 100%;
      min-height: 90px;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ddd;
      resize: vertical;
      font-size: 14px;
    }

    .btn-prim {
      margin-top: 10px;
      padding: 10px 16px;
      border-radius: 10px;
      border: none;
      background: #7062ff;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
    }

    .av-item {
      padding: 12px 0;
      border-bottom: 1px solid #e5e7eb;
    }

    .stars-display {
      display: flex;
      gap: 2px;
      color: #f4c43b;
      margin-top: 3px;
    }

    .btn-back {
      display: block;
      margin: 30px auto 0;
      width: fit-content;
      padding: 10px 18px;
      border-radius: 999px;
      background: #d3ddff;
      color: #333;
      font-weight: 500;
      text-decoration: none;
    }
  </style>
</head>

<body>
<div class="card">

  <div class="card-map" style="background-image:url('<?= $imagem_capa ?>')"></div>

  <div class="card-content">

    <!-- Header -->
    <div class="card-header">

      <div class="title-block">
        <h1><?= htmlspecialchars($nome) ?></h1>

        <div class="tags-row">
          <?php if ($tipo): ?><div class="tag-pill"><?= $tipo ?></div><?php endif; ?>
          <?php if ($faixa): ?><div class="tag-pill"><?= $faixa ?></div><?php endif; ?>
        </div>
      </div>

      <div class="info-block">
        <span><strong>Endereço:</strong> <?= $endereco ?: '—' ?></span>
        <span><strong>Horário:</strong> <?= $horario ?: '—' ?></span>
        <span><strong>Contato:</strong> <?= $telefone ?: '—' ?></span>
        <span><strong>Site:</strong> <?= $site_link ?: '—' ?></span>
      </div>

    </div>

    <!-- Sobre + Serviços -->
    <div class="about-row">

      <div class="about-text">
        <div class="section-title">Sobre o local</div>
        <p><?= $descricao ?: 'Sem descrição.' ?></p>

        <div class="section-title" style="margin-top:20px;">Serviços</div>
        <?php if ($servicos): ?>
          <div class="chips">
            <?php foreach ($servicos as $s): ?>
              <span class="chip"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="muted">Nenhum serviço informado.</p>
        <?php endif; ?>
      </div>

      <div class="info-block">
        <div class="section-title">Redes sociais</div>
        <?php if ($redes_links): ?>
          <?= implode("<br>", $redes_links) ?>
        <?php else: ?>
          <p class="muted">Nenhuma informada.</p>
        <?php endif; ?>
      </div>

    </div>

    <!-- Avaliação geral -->
    <div class="rating-row">
      <div class="stars">
        <?php for ($i=1; $i<=5; $i++): ?>
          <span><?= $i <= round($avaliacao) ? "★" : "☆" ?></span>
        <?php endfor; ?>
      </div>
      <div class="rating-value"><?= number_format($avaliacao,1,",",".") ?></div>
    </div>

    <!-- RF08 botão único -->
    <?php if ($id_usuario_logado): ?>
      <?php if (!$jaMarcou): ?>
        <a href="ir.php?id_local=<?= $id_local ?>" class="reserve-btn">🚶 Estou indo</a>
      <?php else: ?>
        <a href="ir.php?id_local=<?= $id_local ?>&acao=cancelar"
           class="reserve-btn" style="background:#dc2626; box-shadow:0 12px 18px rgba(220,38,38,0.4);">
           ✖ Cancelar ida
        </a>
      <?php endif; ?>
    <?php else: ?>
      <p class="muted" style="text-align:center; margin-top:10px;">Faça login para marcar “Estou indo”.</p>
    <?php endif; ?>

    <!-- RF09: Avaliar -->
    <div class="av-card">
      <h3>Avaliar este local</h3>

      <?php if ($id_usuario_logado): ?>
        <form method="POST" action="avaliar.php">
          <input type="hidden" name="id_local" value="<?= $id_local ?>">

          <div class="rate-row">
            <span>Sua nota:</span>
            <div class="stars-input">
              <?php $nota_sel = (int)($minha_avaliacao['nota'] ?? 0); ?>
              <?php for ($i=5; $i>=1; $i--): ?>
                <input type="radio" id="rate<?= $i ?>" name="nota" value="<?= $i ?>" <?= $nota_sel === $i ? 'checked' : '' ?>>
                <label for="rate<?= $i ?>">★</label>
              <?php endfor; ?>
            </div>
          </div>

          <textarea class="av-text" name="comentario" placeholder="Conte rapidamente sua experiência (opcional)"><?= htmlspecialchars($minha_avaliacao['comentario'] ?? '') ?></textarea>

          <button type="submit" class="btn-prim">
            <?= $minha_avaliacao ? "Atualizar avaliação" : "Enviar avaliação" ?>
          </button>
        </form>
      <?php else: ?>
        <p class="muted">Faça login para avaliar este local.</p>
      <?php endif; ?>
    </div>

    <!-- Lista de avaliações -->
    <div class="av-card">
      <h3>O que a galera achou</h3>

      <?php if ($avaliacoes): ?>
        <?php foreach ($avaliacoes as $av): ?>
          <div class="av-item">
            <strong><?= htmlspecialchars($av['nome']) ?></strong>
            <span class="muted"> · <?= date("d/m/Y", strtotime($av['criado_em'])) ?></span>

            <div class="stars-display">
              <?php for ($i=1; $i<=5; $i++): ?>
                <span><?= $i <= $av['nota'] ? "★" : "☆" ?></span>
              <?php endfor; ?>
            </div>

            <?php if ($av['comentario']): ?>
              <p><?= nl2br(htmlspecialchars($av['comentario'])) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="muted">Ainda não há avaliações.</p>
      <?php endif; ?>
    </div>

    <!-- Voltar -->
    <a href="explorar.php" class="btn-back">← Voltar para Explorar</a>

  </div>
</div>

</body>
</html>