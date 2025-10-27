<?php
require_once __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = pdo();
$id_usuario = $_SESSION['user_id'] ?? null;
$id_local = isset($_GET['id_local']) ? (int)$_GET['id_local'] : 0;
$acao = $_GET['acao'] ?? null;
$confirmar = isset($_GET['confirmar']) ? (int)$_GET['confirmar'] : 0;

if (!$id_usuario) {
    header("Location: ../auth/login.php");
    exit();
}

// Cancelar ida
if (isset($_GET['acao']) && $_GET['acao'] === 'cancelar') {
    $del = $pdo->prepare("DELETE FROM estou_indo WHERE id_usuario = :u");
    $del->execute([':u' => $id_usuario]);
    header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Você cancelou sua ida."));
    exit();
}

// Remove marcações antigas (acima de 12h)
$pdo->prepare("
    DELETE FROM estou_indo
    WHERE TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) >= 12
")->execute();

// Verifica se o usuário já tem uma ida ativa
$check = $pdo->prepare("
    SELECT id_local FROM estou_indo 
    WHERE id_usuario = :u AND TIMESTAMPDIFF(HOUR, data_marcacao, NOW()) < 12
    LIMIT 1
");
$check->execute([':u' => $id_usuario]);
$ativo = $check->fetch(PDO::FETCH_ASSOC);

// Caso o usuário já tenha um local ativo
if ($ativo && !$confirmar) {
    $msg = "Você já tem um local marcado para ir hoje, deseja alterar mesmo assim?";
    $urlSim = "ir.php?id_local=$id_local&confirmar=1";
    $urlNao = "detalhes.php?id={$ativo['id_local']}";
    echo "
        <div style='font-family:Arial;padding:40px;text-align:center;'>
            <h2>$msg</h2>
            <br>
            <a href='$urlSim' style='background:#0d6efd;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>Sim, alterar</a>
            <a href='$urlNao' style='margin-left:10px;color:#334155;text-decoration:none;'>Não</a>
        </div>
    ";
    exit();
}

// Caso o usuário confirme alteração
if ($ativo && $confirmar) {
    $del = $pdo->prepare("DELETE FROM estou_indo WHERE id_usuario = :u");
    $del->execute([':u' => $id_usuario]);
}

// Registrar nova ida
try {
    $insert = $pdo->prepare("
        INSERT INTO estou_indo (id_usuario, id_local, data_marcacao)
        VALUES (:u, :l, NOW())
    ");
    $insert->execute([':u' => $id_usuario, ':l' => $id_local]);

    header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Local marcado com sucesso!"));
    exit();

} catch (PDOException $e) {
    error_log("Erro ao registrar 'Estou indo': " . $e->getMessage());
    header("Location: detalhes.php?id=$id_local&msg=" . urlencode("Erro ao registrar no banco."));
    exit();
}
?>