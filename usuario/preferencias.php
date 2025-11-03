<?php
// /usuario/preferencias.php
declare(strict_types=1);
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: /auth/login.php?msg=" . urlencode("Faça login para editar suas preferências."));
  exit;
}

$pdo = pdo();
$idUsuario = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
  header("Location: /auth/login.php?msg=" . urlencode("Sessão inválida."));
  exit;
}

// carregar tipos existentes
$tipos = $pdo->query("SELECT DISTINCT tipo FROM locais WHERE tipo IS NOT NULL AND tipo <> '' ORDER BY tipo ASC")
             ->fetchAll(PDO::FETCH_COLUMN);

// carregar preferências salvas
$pref = $pdo->prepare("SELECT tipos_csv, horarios_json FROM user_preferences WHERE id_usuario = :u LIMIT 1");
$pref->execute([':u' => $idUsuario]);
$prefRow = $pref->fetch(PDO::FETCH_ASSOC);

$meusTipos = [];
$horarios = ['dias'=>[], 'inicio'=>'', 'fim'=>''];
if ($prefRow) {
  if (!empty($prefRow['tipos_csv'])) $meusTipos = array_map('trim', explode(',', $prefRow['tipos_csv']));
  if (!empty($prefRow['horarios_json'])) {
    $tmp = json_decode($prefRow['horarios_json'], true);
    if (is_array($tmp)) $horarios = array_merge($horarios, $tmp);
  }
}

$DIA_OPTS = ['seg','ter','qua','qui','sex','sab','dom'];

// salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selTipos = isset($_POST['tipos']) && is_array($_POST['tipos']) ? $_POST['tipos'] : [];
  $tipos_csv = implode(',', array_map(fn($t)=>trim((string)$t), $selTipos));

  $dias = isset($_POST['dias']) && is_array($_POST['dias']) ? array_values(array_intersect($DIA_OPTS, $_POST['dias'])) : [];
  $inicio = trim($_POST['inicio'] ?? '');
  $fim    = trim($_POST['fim'] ?? '');
  $horarios_json = json_encode(['dias'=>$dias, 'inicio'=>$inicio, 'fim'=>$fim], JSON_UNESCAPED_UNICODE);

  // upsert simples
  $up = $pdo->prepare("
    INSERT INTO user_preferences (id_usuario, tipos_csv, horarios_json)
    VALUES (:u, :t, :h)
    ON DUPLICATE KEY UPDATE
      tipos_csv = VALUES(tipos_csv),
      horarios_json = VALUES(horarios_json)
  ");
  $up->execute([':u'=>$idUsuario, ':t'=>$tipos_csv, ':h'=>$horarios_json]);

  header("Location: ../usuario/preferencias.php?msg=" . urlencode("Preferências salvas!"));
  exit;
}

$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Minhas preferências — Qual a Boa?</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .wrap{max-width:900px;margin:24px auto;padding:16px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
    .grid{display:grid;gap:12px;grid-template-columns:1fr 1fr}
    label{display:flex;gap:8px;align-items:center;margin:6px 0}
    .chip{display:inline-block;padding:6px 10px;border:1px solid #e5e7eb;border-radius:999px;margin:4px 6px;background:#f8fafc}
    .btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid #e5e7eb;background:#0d6efd;color:#fff;text-decoration:none}
    .btn:hover{background:#0b5ed7}
    .success{background:#ecfdf5;border:1px solid #a7f3d0;padding:10px;border-radius:8px;color:#065f46;margin-bottom:10px}
    @media(max-width:800px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Minhas preferências</h1>
      <?php if($msg): ?><div class="success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <form method="post">
        <h3>Tipos favoritos</h3>
        <div>
          <?php foreach ($tipos as $t): ?>
            <?php $checked = in_array($t, $meusTipos, true) ? 'checked' : ''; ?>
            <label><input type="checkbox" name="tipos[]" value="<?= htmlspecialchars($t) ?>" <?= $checked ?>> <span class="chip"><?= htmlspecialchars($t) ?></span></label>
          <?php endforeach; ?>
          <?php if (!$tipos): ?><p class="muted">Ainda não há tipos cadastrados.</p><?php endif; ?>
        </div>

        <hr style="margin:14px 0">

        <h3>Horário típico que você sai</h3>
        <div class="grid">
          <div>
            <p>Dias:</p>
            <?php foreach ($DIA_OPTS as $d): ?>
              <label>
                <input type="checkbox" name="dias[]" value="<?= $d ?>" <?= in_array($d, $horarios['dias']??[], true) ? 'checked' : '' ?>>
                <span class="chip"><?= strtoupper($d) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div>
            <p>Janela (opcional):</p>
            <label>Início: <input type="time" name="inicio" value="<?= htmlspecialchars($horarios['inicio']??'') ?>"></label>
            <label>Fim: <input type="time" name="fim" value="<?= htmlspecialchars($horarios['fim']??'') ?>"></label>
          </div>
        </div>

        <div style="margin-top:16px;">
          <button class="btn" type="submit">Salvar preferências</button>
          <a class="btn" style="background:#fff;color:#111;border-color:#e5e7eb" href="../feed.php">Ir para o feed</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
