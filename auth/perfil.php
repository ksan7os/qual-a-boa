<?php
// usuario/perfil.php
// Perfil do usuário + RF08 ("Estou indo" ativo nas últimas 12h)

require __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../bd/auth.php';

// exige login
require_login();

$pdo = pdo();
if (!$pdo) { die('Erro de conexão.'); }

// pega id do usuário logado
$userId = (int)($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);

// dados do usuário
$stmt = $pdo->prepare('SELECT nome, email, data_criacao, foto_perfil FROM usuario WHERE id_usuario = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$nome          = $row['nome']         ?? ($_SESSION['user_name'] ?? 'Usuário');
$email         = $row['email']        ?? 'Email não disponível';
$data_criacao  = $row['data_criacao'] ?? date('Y-m-d H:i:s');
$foto_perfil   = $row['foto_perfil']  ?? 'default-profile.jpg';
$data_criacao_formatada = date('d/m/Y', strtotime($data_criacao));

/* ========================== RF08: “Estou indo” (APENAS ATIVOS) ==========================
   - Não apagamos mais registros.
   - Exibimos somente os ativos:
     * ainda não cancelados (desmarcado_em IS NULL)
     * dentro da janela de 12h desde a marcação
========================================================================================= */

$id_usuario = (int)($_SESSION['id_usuario'] ?? $userId);

$sqlEstouIndo = "
    SELECT 
        ei.data_marcacao,
        l.id_local,
        l.nome,
        l.tipo,
        l.endereco,
        l.faixa_preco,
        l.avaliacao_media,
        l.imagem_capa
    FROM estou_indo ei
    INNER JOIN locais l ON ei.id_local = l.id_local
    WHERE ei.id_usuario = :id_usuario
      AND ei.desmarcado_em IS NULL
      AND TIMESTAMPDIFF(HOUR, ei.data_marcacao, NOW()) < 12
    ORDER BY ei.data_marcacao DESC
";
$stmtEstouIndo = $pdo->prepare($sqlEstouIndo);
$stmtEstouIndo->execute([':id_usuario' => $id_usuario]);
$locaisMarcados = $stmtEstouIndo->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f5f6f8;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .main-container {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
            padding: 30px 24px;
        }
        h1 { font-size: 26px; margin-bottom: 20px; }
        .perfil-container {
            display: flex; flex-direction: column; align-items: flex-start;
            gap: 6px; margin-bottom: 24px;
        }
        .foto-perfil {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; margin-bottom: 10px; border: 3px solid #e2e8f0;
        }
        .link-button {
            display: inline-block; background: #0d6efd; color: #fff;
            padding: 10px 16px; border-radius: 10px; text-decoration: none;
            font-weight: 500; margin-right: 10px; transition: background .2s ease;
        }
        .link-button:hover { background: #0b5ed7; }

        /* ===== RF08: seção “Estou indo” ===== */
        .estou-indo-section { margin-top: 40px; }
        .estou-indo-section h2 { font-size: 20px; color: #1e293b; margin-bottom: 12px; }
        .locais-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;
        }
        .local-card {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 12px; font-size: 0.9rem; color: #334155;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        .local-card img {
            width: 100%; height: 140px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;
        }
        .local-card h3 { font-size: 1rem; font-weight: 600; margin: 4px 0; color: #1e293b; }
        .local-card .meta { color: #475569; font-size: 0.9rem; }
        .local-card .meta small { display: block; color: #64748b; font-size: 0.8rem; }
        .local-card .avaliacao { margin-top: 6px; font-size: 0.8rem; color: #0f172a; }
        .local-card .data { margin-top: 8px; font-size: 0.75rem; color: #64748b; }
        .btn-ver {
            display: inline-block; background: #2563eb; color: #fff; padding: 8px 12px;
            border-radius: 8px; font-size: 0.8rem; font-weight: 500; text-decoration: none;
            margin-top: 10px; box-shadow: 0 4px 10px rgba(37,99,235,0.4);
        }
        .btn-ver:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="main-container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?>!</h1>
        
        <div class="perfil-container">
            <img src="../img/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" class="foto-perfil">
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Data de Criação:</strong> <?php echo $data_criacao_formatada; ?></p>
        </div>

        <a class="link-button" href="editar-perfil.php">Editar Perfil</a>
        <a class="link-button" href="../usuario/historico.php">Ver meu histórico</a>
        <a class="link-button" href="../dashboard.php">Voltar</a>
        <a class="link-button" href="logout.php">Logout</a>

        <!-- ================= RF08: Locais marcados como “Estou indo” (somente ativos) ================= -->
        <div class="estou-indo-section">
            <h2>Meus rolês marcados (“Estou indo”)</h2>

            <?php if (empty($locaisMarcados)): ?>
                <p style="color:#64748b;">Você não tem nenhuma ida ativa no momento.</p>
            <?php else: ?>
                <div class="locais-grid">
                    <?php foreach ($locaisMarcados as $m): ?>
                        <div class="local-card">
                            <?php if (!empty($m['imagem_capa'])): ?>
                                <img src="../img/capa-locais/<?php echo htmlspecialchars($m['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($m['nome']); ?>">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($m['nome']); ?></h3>
                            <div class="meta">
                                <?php echo htmlspecialchars($m['tipo']); ?> · <?php echo htmlspecialchars($m['faixa_preco']); ?>
                                <small><?php echo htmlspecialchars($m['endereco']); ?></small>
                            </div>
                            <div class="avaliacao">⭐ <?php echo number_format((float)$m['avaliacao_media'], 1, ',', '.'); ?> média</div>
                            <div class="data">
                                Marcado em:
                                <?php
                                    $data = new DateTime($m['data_marcacao']);
                                    echo $data->format('d/m/Y H:i');
                                ?>
                            </div>
                            <a href="../locais/detalhes.php?id=<?php echo (int)$m['id_local']; ?>" class="btn-ver">Ver detalhes</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <!-- =========================================================================== -->
    </div>
</body>
</html>
