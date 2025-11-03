<?php
// locais/ir.php — marcar/cancelar "Estou indo" com histórico e janela de 12h
declare(strict_types=1);

require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('is_logged_in') || !is_logged_in()) {
  header("Location: /auth/login.php?msg=" . urlencode("Faça login para continuar."));
  exit;
}

$pdo = pdo();

$idUsuario = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
$idLocal   = (int)($_POST['id_local'] ?? $_GET['id_local'] ?? 0);
$acao      = $_GET['acao'] ?? $_POST['acao'] ?? 'marcar';
$forcar    = isset($_GET['forcar']) ? (int)$_GET['forcar'] : 0;

if ($idUsuario <= 0 || $idLocal <= 0) {
  header("Location: /index.php?msg=" . urlencode("Dados inválidos."));
  exit;
}

/**
 * Helper para voltar à página do local
 */
function voltarDetalhes(int $idLocal, string $msg) {
  header("Location: ../locais/detalhes.php?id={$idLocal}&msg=" . urlencode($msg));
  exit;
}

/**
 * 1) Ação: cancelar (NÃO deve exibir confirmação, apenas encerrar o ativo)
 */
if ($acao === 'cancelar') {
  // encerra o ativo (se houver) para este local/usuário
  $stmt = $pdo->prepare("
    UPDATE estou_indo
       SET desmarcado_em = NOW(),
           desmarcado_motivo = 'manual'
     WHERE id_usuario = :u
       AND id_local   = :l
       AND desmarcado_em IS NULL
  ");
  $stmt->execute([':u' => $idUsuario, ':l' => $idLocal]);

  voltarDetalhes($idLocal, "Marcador removido.");
}

/**
 * 2) Ação: marcar (padrão)
 */

// expira automaticamente qualquer ativo com mais de 12h
$pdo->prepare("
  UPDATE estou_indo
     SET desmarcado_em = NOW(),
         desmarcado_motivo = 'auto'
   WHERE id_usuario = :u
     AND desmarcado_em IS NULL
     AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) > 12
")->execute([':u' => $idUsuario]);

// existe algo ativo (<=12h) para ESTE usuário?
$sel = $pdo->prepare("
  SELECT id_local
    FROM estou_indo
   WHERE id_usuario = :u
     AND desmarcado_em IS NULL
     AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) <= 12
   ORDER BY data_marcacao DESC
   LIMIT 1
");
$sel->execute([':u' => $idUsuario]);
$ativo = $sel->fetchColumn();

if ($ativo) {
  $idAtivo = (int)$ativo;

  // Se já está ativo no mesmo local, só avisa e volta
  if ($idAtivo === $idLocal) {
    voltarDetalhes($idLocal, "Você já marcou este local como 'Estou indo'.");
  }

  // Se está ativo em OUTRO local e não está forçando, pede confirmação
  if (!$forcar) {
    // Mostra página simples de confirmação
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
      <meta charset="utf-8">
      <title>Confirmar alteração</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding: 40px; }
        .wrap { max-width: 720px; margin: 0 auto; text-align: center; }
        h1 { font-weight: 700; }
        .actions { margin-top: 16px; display: flex; gap: 10px; justify-content: center; }
        .btn { padding: 10px 16px; border-radius: 8px; border: 1px solid #d1d5db; text-decoration: none; }
        .btn-primary { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
        .btn:hover { opacity: .9; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <h1>Você já tem um local marcado para hoje, deseja alterar mesmo assim?</h1>
        <div class="actions">
          <a class="btn btn-primary" href="../locais/ir.php?id_local=<?php echo (int)$idLocal; ?>&forcar=1">Sim, alterar</a>
          <a class="btn" href="../locais/detalhes.php?id=<?php echo (int)$idAtivo; ?>">Não</a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
  }

  // Se chegou aqui com forçar=1, encerramos o antigo e seguimos
  $pdo->prepare("
    UPDATE estou_indo
       SET desmarcado_em = NOW(),
           desmarcado_motivo = 'manual'
     WHERE id_usuario = :u
       AND id_local   = :l
       AND desmarcado_em IS NULL
  ")->execute([':u' => $idUsuario, ':l' => $idAtivo]);
}

// Cria NOVO registro para o local atual (histórico permanente)
$ins = $pdo->prepare("
  INSERT INTO estou_indo (id_usuario, id_local, data_marcacao, desmarcado_em, desmarcado_motivo)
  VALUES (:u, :l, NOW(), NULL, NULL)
");
$ins->execute([':u' => $idUsuario, ':l' => $idLocal]);

voltarDetalhes($idLocal, "Marcado como 'Estou indo'!");
