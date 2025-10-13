<?php
// locais/detalhes.php — RF07 com todos os campos do CRUD
require __DIR__ . '/../bd/conexao.php';
$pdo = pdo();
if (!$pdo) { die("Erro de conexão com o banco de dados."); }

// 1) validação do id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<h3>Local inválido.</h3>";
  exit;
}
$id_local = (int)$_GET['id'];

// 2) busca
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

// 3) preparação dos dados (com fallback para evitar avisos)
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

// helpers de exibição
function explode_list($str) {
  if (!$str) return [];
  $parts = array_map('trim', explode(',', $str));
  return array_values(array_filter($parts, fn($v) => $v !== ''));
}

// serviços como chips
$servicos = array_map('htmlspecialchars', explode_list($servicos_raw));

// redes sociais: torna links clicáveis (se não tiver http, adiciona)
$redes = explode_list($redes_raw);
$redes_links = array_map(function($link) {
  $link = trim($link);
  $label = htmlspecialchars($link);
  if (!preg_match('~^https?://~i', $link)) { $link = 'https://' . $link; }
  $href = htmlspecialchars($link);
  return "<a href=\"$href\" target=\"_blank\" rel=\"noopener noreferrer\">$label</a>";
}, $redes);

// site clicável (opcional)
$site_link = '';
if ($site !== '') {
  $href = htmlspecialchars((preg_match('~^https?://~i', $site) ? $site : "https://$site"));
  $site_link = "<a href=\"$href\" target=\"_blank\" rel=\"noopener noreferrer\">$href</a>";
}
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

        <a href="explorar.php" class="btn-voltar">← Voltar para Explorar</a>
      </div>
    </div>
  </div>
</body>
</html>