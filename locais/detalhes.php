<?php
// locais/detalhes.php — RF07 (detalhes) + RF09 (avaliações)
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

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

// minha avaliação (para pré-preencher)
$minha_avaliacao = null;
if ($id_usuario_logado) {
  $st = $pdo->prepare("SELECT nota, comentario FROM avaliacoes WHERE id_local = ? AND id_usuario = ?");
  $st->execute([$id_local, $id_usuario_logado]);
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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= $nome ?> — Detalhes do Local</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body { background:#f5f6f8; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    .detalhes-wrap { max-width: 1040px; margin: 40px auto; padding: 0 16px; }
    .card {
      background:#fff; border-radius:16px; box-shadow:0 6px 18px rgba(0,0,0,.08);
      overflow:hidden;
    }
    .hero { width:100%; height:380px; object-fit:cover; display:block; }
    .content { padding:24px; }
    h1 { margin:8px 0 16px; font-size:28px; color:#1d1f23; }
    .meta { display:flex; flex-wrap:wrap; gap:16px; color:#4a4f57; margin-bottom:18px; }
    .meta span { background:#f1f3f5; padding:6px 10px; border-radius:8px; font-size:14px; }
    .grid { display:grid; grid-template-columns: 1.2fr .8fr; gap:24px; }
    .section h3 { margin:10px 0 8px; font-size:18px; color:#1d1f23; }
    .muted { color:#555; }
    .chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .chip {
      background:#eef4ff; color:#1d4ed8; border:1px solid #dbe7ff;
      padding:6px 10px; border-radius:999px; font-size:13px;
    }
    .list { line-height:1.6; color:#333; }
    .links a { color:#0a66c2; text-decoration:none; }
    .links a:hover { text-decoration:underline; }
    .btn-voltar {
      display:inline-block; margin:20px 0 8px; background:#0d6efd; color:#fff;
      padding:10px 16px; border-radius:10px; text-decoration:none;
    }
    .btn-voltar:hover { background:#0b5ed7; }
    @media (max-width: 900px) {
      .grid { grid-template-columns: 1fr; }
      .hero { height:280px; }
    }

    /* ===== RF09 ===== */
    .flash   { margin: 12px 0; color:#065f46; background:#ecfdf5; border:1px solid #a7f3d0; padding:8px 10px; border-radius:8px; display:inline-block;}
    .av-card { margin-top: 24px; padding: 16px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; }
    .rate-row { display:flex; gap:8px; align-items:center; margin:8px 0 12px; }
    .stars-input { display:flex; flex-direction: row-reverse; gap:6px; }
    .stars-input input { display:none; }
    .stars-input label { font-size: 24px; cursor: pointer; user-select:none; filter: grayscale(25%); transition: transform .1s ease; }
    .stars-input input:checked ~ label { filter: grayscale(0%); }
    .stars-input label:hover { transform: scale(1.05); }
    textarea.av-text { width:100%; min-height:90px; padding:10px; border:1px solid #e5e7eb; border-radius:8px; resize: vertical; }
    .btn-prim { display:inline-block; border:none; background:#0d6efd; color:#fff; padding:10px 14px; border-radius:10px; cursor:pointer; }
    .btn-prim:hover { background:#0b5ed7; }

    .av-item { padding:12px 0; border-bottom:1px solid #e5e7eb; }
    .av-item:last-child { border-bottom:none; }
    .stars-display { display:flex; gap:2px; }
    .stars-display .s { font-size:16px; color:#f59e0b; }
  </style>
</head>
<body>
  <div class="detalhes-wrap">
    <div class="card">
      <img class="hero" src="<?= $imagem_capa ?>" alt="Imagem de <?= $nome ?>">

      <div class="content">
        <h1><?= $nome ?></h1>

        <div class="meta">
          <span><strong>Tipo:</strong> <?= $tipo ?: '—' ?></span>
          <span><strong>Preço:</strong> <?= $faixa ?: '—' ?></span>
          <span><strong>Avaliação:</strong> <?= $avaliacao ?>/5</span>
        </div>

        <div class="grid">
          <!-- coluna esquerda -->
          <div class="section">
            <h3>Sobre</h3>
            <p class="list"><?= $descricao ?: '<span class="muted">Sem descrição.</span>' ?></p>

            <h3 style="margin-top:18px;">Serviços</h3>
            <?php if ($servicos): ?>
              <div class="chips">
                <?php foreach ($servicos as $tag): ?>
                  <span class="chip"><?= $tag ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="muted">Nenhum serviço informado.</p>
            <?php endif; ?>

            <h3 style="margin-top:18px;">Horário de funcionamento</h3>
            <p class="list"><?= $horario ?: '<span class="muted">Não informado.</span>' ?></p>
          </div>

          <!-- coluna direita -->
          <div class="section">
            <h3>Localização</h3>
            <p class="list"><?= $endereco ?: '<span class="muted">Não informado.</span>' ?></p>

            <h3 style="margin-top:18px;">Contato</h3>
            <p class="list"><strong>Site:</strong> <span class="links"><?= $site_link ?: '<span class="muted">—</span>' ?></span></p>
            <p class="list"><strong>Número:</strong> <?= $telefone ?: '<span class="muted">—</span>' ?></p>
            <p class="list"><strong>E-mail:</strong> <?= $email ?: '<span class="muted">—</span>' ?></p>

            <h3 style="margin-top:18px;">Redes sociais</h3>
            <?php if ($redes_links): ?>
              <div class="list links" style="display:flex; flex-direction:column; gap:6px;">
                <?= implode('<br>', $redes_links) ?>
              </div>
            <?php else: ?>
              <p class="muted">Nenhuma rede social informada.</p>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($_GET['msg'])): ?>
          <div class="flash"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <!-- ==================== RF09: Formulário de avaliação ==================== -->
        <div class="av-card">
          <h3>Avaliar este local</h3>

          <?php if ($id_usuario_logado): ?>
            <form method="POST" action="avaliar.php">
              <input type="hidden" name="id_local" value="<?= (int)$id_local ?>">

              <div class="rate-row">
                <span class="muted">Sua nota:</span>
                <div class="stars-input">
                  <?php $nota_sel = (int)($minha_avaliacao['nota'] ?? 0); ?>
                  <?php for ($i=5; $i>=1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="nota" value="<?= $i ?>" <?= $nota_sel === $i ? 'checked' : '' ?>>
                    <label for="star<?= $i ?>">★</label>
                  <?php endfor; ?>
                </div>
              </div>

              <textarea name="comentario" class="av-text" placeholder="Conte rapidamente sua experiência (opcional)"><?= htmlspecialchars($minha_avaliacao['comentario'] ?? '') ?></textarea>
              <br>
              <button class="btn-prim" type="submit"><?= $minha_avaliacao ? 'Atualizar avaliação' : 'Enviar avaliação' ?></button>
            </form>
          <?php else: ?>
            <p class="muted">Faça login para avaliar este local.</p>
          <?php endif; ?>
        </div>
        <!-- ===================================================================== -->

        <!-- ==================== RF09: Lista de avaliações ====================== -->
        <div class="av-card">
          <h3>O que a galera achou</h3>

          <?php if (!$avaliacoes): ?>
            <p class="muted">Ainda não há avaliações.</p>
          <?php else: ?>
            <?php foreach ($avaliacoes as $av): ?>
              <div class="av-item">
                <div style="display:flex; gap:8px; align-items:center;">
                  <strong><?= htmlspecialchars($av['nome']) ?></strong>
                  <span class="muted">· <?= date('d/m/Y', strtotime($av['criado_em'])) ?></span>
                </div>
                <div class="stars-display" aria-label="Nota <?= (int)$av['nota'] ?> de 5">
                  <?php for ($i=1; $i<=5; $i++): ?>
                    <span class="s"><?= $i <= (int)$av['nota'] ? '★' : '☆' ?></span>
                  <?php endfor; ?>
                  <span class="muted" style="margin-left:6px;"><?= (int)$av['nota'] ?>/5</span>
                </div>
                <?php if (!empty($av['comentario'])): ?>
                  <p style="margin-top:6px;"><?= nl2br(htmlspecialchars($av['comentario'])) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- ===================================================================== -->

        <a href="explorar.php" class="btn-voltar">← Voltar para Explorar</a>
      </div>
    </div>
  </div>
</body>
</html>
