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

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    body {
      min-height: 100vh;
      background: radial-gradient(circle at bottom, #9c1fd4 0%, #000 60%);
      display: flex;
      justify-content: center;
      padding: 40px 20px;
    }

    .wrap {
      width: 100%;
      max-width: 900px;
      color: #fff;
    }

    .card {
      background: #ffffff;
      border-radius: 18px;
      padding: 32px 36px;
      box-shadow: 0 18px 36px rgba(0, 0, 0, 0.45);
      color: #111;
    }

    h1 {
      font-size: 30px;
      margin-bottom: 20px;
      font-weight: 600;
      color: #3d0a80;
    }

    h3 {
      margin-bottom: 12px;
      font-size: 18px;
      color: #2d0b63;
    }

    .success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      padding: 12px;
      border-radius: 10px;
      color: #065f46;
      margin-bottom: 16px;
      font-size: 14px;
    }

    /* Chips + checkboxes */
    label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      margin: 6px 0;
    }

    input[type="checkbox"] {
      transform: scale(1.2);
      accent-color: #7b2dff;
      cursor: pointer;
    }

    .chip {
      background: #e9ddff;
      padding: 7px 14px;
      border-radius: 999px;
      font-size: 13px;
      color: #3a2b7a;
      font-weight: 500;
      border: 1px solid #cfc4ff;
    }

    .muted {
      color: #555;
      font-size: 13px;
    }

    hr {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 20px 0;
    }

    /* Grid para dias e horário */
    .grid {
      display: grid;
      gap: 18px;
      grid-template-columns: 1fr 1fr;
    }

    @media(max-width: 700px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }

    input[type="time"] {
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 12px;
      font-size: 14px;
      width: 100%;
      outline: none;
      background: #fff;
      margin-left: 6px;
    }

    /* Botões */
    .btn-row {
      margin-top: 22px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 12px 22px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      text-align: center;
      transition: 0.15s ease;
      box-shadow: 0 6px 12px rgba(0,0,0,0.25);
    }

    .btn-save {
      background: #6c1bff;
      color: #fff;
    }

    .btn-save:hover {
      background: #5515cc;
    }

    .btn-white {
      background: #fff;
      color: #222;
      border: 1px solid #ddd;
    }

    .btn-white:hover {
      background: #eaeaea;
    }
  </style>
</head>

<body>
<div class="wrap">
  <div class="card">

    <h1>Minhas preferências</h1>

    <?php if($msg): ?>
      <div class="success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">

      <!-- Tipos favoritos -->
      <h3>Tipos favoritos</h3>
      <div>
        <?php foreach ($tipos as $t): ?>
          <?php $checked = in_array($t, $meusTipos, true) ? 'checked' : ''; ?>
          <label>
            <input type="checkbox" name="tipos[]" value="<?= htmlspecialchars($t) ?>" <?= $checked ?>>
            <span class="chip"><?= htmlspecialchars($t) ?></span>
          </label>
        <?php endforeach; ?>

        <?php if (!$tipos): ?>
          <p class="muted">Ainda não há tipos cadastrados.</p>
        <?php endif; ?>
      </div>

      <hr>

      <!-- Horário típico -->
      <h3>Horário típico que você sai</h3>

      <div class="grid">
        <!-- Dias -->
        <div>
          <p style="margin-bottom: 6px; color: #2d0b63; font-weight: 600;">Dias:</p>

          <?php foreach ($DIA_OPTS as $d): ?>
            <label>
              <input type="checkbox" name="dias[]" value="<?= $d ?>" 
              <?= in_array($d, $horarios['dias'] ?? [], true) ? 'checked' : '' ?>>
              <span class="chip"><?= strtoupper($d) ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Janela -->
        <div>
          <p style="margin-bottom: 6px; color: #2d0b63; font-weight: 600;">Janela (opcional):</p>

          <label>Início:
            <input type="time" name="inicio" value="<?= htmlspecialchars($horarios['inicio'] ?? '') ?>">
          </label>

          <label style="margin-top: 8px;">Fim:
            <input type="time" name="fim" value="<?= htmlspecialchars($horarios['fim'] ?? '') ?>">
          </label>
        </div>
      </div>

      <!-- Botões -->
      <div class="btn-row">
        <button type="submit" class="btn btn-save">Salvar preferências</button>
        <a href="../feed.php" class="btn btn-white">Ir para o feed</a>
      </div>

    </form>

  </div>
</div>
</body>
</html>