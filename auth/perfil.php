<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Verificar se o usuário está logado
require_login();

// Obter o ID do usuário da sessão
$pdo = pdo();
$userId = (int)$_SESSION['user_id'];

// Buscar os dados do usuário (nome, email, data_criacao, foto_perfil)
$stmt = $pdo->prepare('SELECT nome, email, data_criacao, foto_perfil FROM usuario WHERE id_usuario = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch();

// Atribuir valores às variáveis
$nome = $row ? $row['nome'] : ($_SESSION['user_name'] ?? 'Usuário');
$email = $row['email'] ?? 'Email não disponível';
$data_criacao = $row['data_criacao'] ?? 'Data não disponível';

// Definir uma foto de perfil padrão se o usuário não tiver uma
$foto_perfil = $row['foto_perfil'] ?? 'default-profile.jpg';

// Formatar a data de criação (opcional)
$data_criacao_formatada = date("d/m/Y", strtotime($data_criacao));

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="../css/style.css">

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
        <a class="link-button "href="../dashboard.php">Voltar</a>
        <a class="link-button" href="logout.php">Logout</a>
    </div>
</body>
</html>
