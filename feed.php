<?php
// /feed.php — Feed em formato de CARD ÚNICO (com fallbacks)
declare(strict_types=1);
require_once __DIR__ . '/bd/conexao.php';
require_once __DIR__ . '/bd/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: ./auth/login.php?msg=" . urlencode("Faça login para ver seu feed."));
  exit;
}

// Anti-cache para não “prender” o mesmo card
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$pdo = pdo();
$idUsuario = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
  header("Location: ./auth/login.php?msg=" . urlencode("Sessão inválida."));
  exit;
}

// carrega preferências
$prefs = $pdo->prepare("SELECT tipos_csv, horarios_json FROM user_preferences WHERE id_usuario = ? LIMIT 1");
$prefs->execute([$idUsuario]);
$p = $prefs->fetch(PDO::FETCH_ASSOC);
$tiposCsv = $p['tipos_csv'] ?? null;

// ===== Query base (evita vistos hoje e "skip hoje") =====
$sqlBase = "
SELECT
  l.id_local, l.nome, l.tipo, l.faixa_preco, l.endereco, l.avaliacao_media, l.imagem_capa,
  (
    CASE
      WHEN p.tipos IS NOT NULL AND p.tipos <> '' AND FIND_IN_SET(l.tipo, p.tipos) > 0 THEN 3
      ELSE 0
    END
    +
    LEAST(GREATEST(FLOOR((COALESCE(l.avaliacao_media,0)-3.0)/0.5), 0), 4)
    -
    CASE WHEN EXISTS (
      SELECT 1 FROM feed_feedback f
       WHERE f.id_usuario = ? AND f.id_local = l.id_local
         AND f.acao = 'skip' AND f.criado_em >= NOW() - INTERVAL 14 DAY
    ) THEN 5 ELSE 0 END
  ) AS score
FROM locais l
CROSS JOIN (SELECT ? AS tipos) p
WHERE 1=1
  AND NOT EXISTS (  -- evita repetidos do dia (view/open)
    SELECT 1 FROM feed_feedback f2
     WHERE f2.id_usuario = ?
       AND f2.id_local   = l.id_local
       AND f2.acao IN ('view','open')
       AND f2.criado_em >= CURDATE()
  )
  AND NOT EXISTS (  -- evita mostrar um que foi skipado hoje
    SELECT 1 FROM feed_feedback fs
     WHERE fs.id_usuario = ?
       AND fs.id_local   = l.id_local
       AND fs.acao = 'skip'
       AND fs.criado_em >= CURDATE()
  )
ORDER BY score DESC, l.avaliacao_media DESC, l.id_local DESC
LIMIT 1
";

// ===== Fallback 1: ignora “vistos hoje”, mas ainda evita “skip hoje” =====
$sqlFallback1 = "
SELECT
  l.id_local, l.nome, l.tipo, l.faixa_preco, l.endereco, l.avaliacao_media, l.imagem_capa,
  (
    CASE
      WHEN p.tipos IS NOT NULL AND p.tipos <> '' AND FIND_IN_SET(l.tipo, p.tipos) > 0 THEN 3
      ELSE 0
    END
    +
    LEAST(GREATEST(FLOOR((COALESCE(l.avaliacao_media,0)-3.0)/0.5), 0), 4)
    -
    CASE WHEN EXISTS (
      SELECT 1 FROM feed_feedback f
       WHERE f.id_usuario = ? AND f.id_local = l.id_local
         AND f.acao = 'skip' AND f.criado_em >= NOW() - INTERVAL 14 DAY
    ) THEN 5 ELSE 0 END
  ) AS score
FROM locais l
CROSS JOIN (SELECT ? AS tipos) p
WHERE 1=1
  AND NOT EXISTS (  -- ainda evita “skip hoje”
    SELECT 1 FROM feed_feedback fs
     WHERE fs.id_usuario = ?
       AND fs.id_local   = l.id_local
       AND fs.acao = 'skip'
       AND fs.criado_em >= CURDATE()
  )
ORDER BY score DESC, l.avaliacao_media DESC, l.id_local DESC
LIMIT 1
";

// ===== Fallback 2: top 1 por avaliação (pode repetir / ignorar preferências) =====
$sqlFallback2 = "
SELECT
  l.id_local, l.nome, l.tipo, l.faixa_preco, l.endereco, l.avaliacao_media, l.imagem_capa,
  0 AS score
FROM locais l
ORDER BY l.avaliacao_media DESC, l.id_local DESC
LIMIT 1
";

// executa base
$stmt = $pdo->prepare($sqlBase);
$stmt->execute([
  $idUsuario,                                           // (p/ penalidade skip-14d)
  ($tiposCsv === null || $tiposCsv === '') ? null : $tiposCsv, // tipos CSV
  $idUsuario,                                           // (visto hoje)
  $idUsuario                                            // (skip hoje)
]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// failsafe: se acabou de pular e veio o mesmo card por cache/latência
if (!empty($_SESSION['last_skip_local']) && $item && (int)$item['id_local'] === (int)$_SESSION['last_skip_local']) {
  unset($_SESSION['last_skip_local']);
  header("Location: ./feed.php"); // reload limpo
  exit;
}

// se não veio nada, fallback 1
if (!$item) {
  $stmt = $pdo->prepare($sqlFallback1);
  $stmt->execute([
    $idUsuario,                                           // (p/ penalidade skip-14d)
    ($tiposCsv === null || $tiposCsv === '') ? null : $tiposCsv, // tipos CSV
    $idUsuario                                            // (skip hoje)
  ]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// se ainda não veio, fallback 2
if (!$item) {
  $stmt = $pdo->query($sqlFallback2);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// loga view (somente se veio algo)
if ($item && !empty($item['id_local'])) {
  $log = $pdo->prepare("INSERT INTO feed_feedback (id_usuario, id_local, acao) VALUES (?,?, 'view')");
  $log->execute([$idUsuario, (int)$item['id_local']]);
}

$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';

// helper para imagem de capa
function capa_src(?string $nome): string {
  $nome = trim((string)$nome);
  if ($nome === '') return "./img/default-profile.jpg";
  return "./img/capa-locais/" . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Feed — Qual a Boa?</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="./css/style.css">
  <link rel="stylesheet" href="./css/explorar.css"><!-- herdando visual -->
  <style>
    .feed-wrap{max-width:900px;margin:24px auto;padding:0 16px}
    .card-unique{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 8px 18px rgba(0,0,0,.06)}
    .card-unique img{width:100%;height:340px;object-fit:cover;display:block}
    .card-body{padding:16px}
    .title{font-weight:800;font-size:22px;margin-bottom:6px}
    .meta{font-size:13px;color:#64748b;margin:4px 0 10px}
    .actions{display:flex;gap:10px;margin-top:12px}
    .btn{display:inline-block;padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none}
    .btn-primary{background:#0d6efd;color:#fff;border-color:#0d6efd}
    .btn-danger{background:#ef4444;color:#fff;border-color:#ef4444}
    .success{background:#ecfdf5;border:1px solid #a7f3d0;padding:10px;border-radius:8px;color:#065f46;margin-bottom:10px}
    .muted{color:#64748b}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
  </style>
</head>
<body>
  <div class="feed-wrap">
    <div class="topbar">
      <h1>Seu feed</h1>
      <div>
        <a class="btn" href="./usuario/preferencias.php">Preferências</a>
        <a class="btn" href="./locais/explorar.php">Explorar</a>
      </div>
    </div>

    <?php if($msg): ?><div class="success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <?php if (!$item): ?>
      <p class="muted">Sem sugestões agora. Tente ajustar as preferências ou voltar mais tarde.</p>
    <?php else: ?>
      <div class="card-unique">
        <img src="<?= capa_src($item['imagem_capa'] ?? '') ?>" alt="Capa de <?= htmlspecialchars($item['nome']) ?>">
        <div class="card-body">
          <div class="title"><?= htmlspecialchars($item['nome']) ?></div>
          <div class="meta">
            <?= htmlspecialchars($item['tipo'] ?? '—') ?>
            <?php if (!empty($item['faixa_preco'])) echo ' • '.htmlspecialchars($item['faixa_preco']); ?>
            <?php if (isset($item['avaliacao_media'])) echo ' • '.number_format((float)$item['avaliacao_media'],1,',','.').' ★'; ?>
          </div>
          <?php if (!empty($item['endereco'])): ?>
            <div class="meta"><?= htmlspecialchars($item['endereco']) ?></div>
          <?php endif; ?>

          <div class="actions">
            <a class="btn btn-primary"
               href="./locais/detalhes.php?id=<?= (int)$item['id_local'] ?>"
               onclick="navigator.sendBeacon('./api/open.php?id_local=<?= (int)$item['id_local'] ?>')">
              Ver mais detalhes
            </a>

            <!-- Próximo = 'skip' -->
            <form action="./api/skip.php" method="post" style="display:inline;">
              <input type="hidden" name="id_local" value="<?= (int)$item['id_local'] ?>">
              <button class="btn btn-danger" type="submit">Próximo</button>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <a class="link-button" href="<?= url('./dashboard.php') ?>">Voltar para o menu</a>
</body>
</html>