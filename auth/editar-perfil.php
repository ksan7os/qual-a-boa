<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Verificar se o usuário está logado
require_login();

// Obter o ID do usuário da sessão
$pdo = pdo();
$userId = (int)$_SESSION['user_id'];

// Buscar os dados do usuário (nome, email, foto_perfil)
$stmt = $pdo->prepare('SELECT nome, email, foto_perfil, senha FROM usuario WHERE id_usuario = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch();

// Atribuir valores às variáveis
$nome = $row['nome'] ?? '';
$email = $row['email'] ?? '';
$foto_perfil = $row['foto_perfil'] ?? 'default-profile.jpg';
$senha_atual = $row['senha'] ?? ''; // Armazenar a senha atual (usada para verificação)
$mensagem_erro = ''; // Para exibir erros de validação ou falhas
$mensagem_sucesso = ''; // Para mensagens de sucesso

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = trim($_POST['nome']);
    $novo_email = trim($_POST['email']);
    $nova_senha = $_POST['senha'];
    $nova_senha_confirmacao = $_POST['senha_confirmacao'];
    $nova_foto = $_FILES['foto_perfil'];

    // Validar os dados
    if (empty($novo_nome) || empty($novo_email)) {
        $mensagem_erro = "Nome e E-mail são obrigatórios.";
    } elseif (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "E-mail inválido.";
    } elseif (!empty($nova_senha) && $nova_senha !== $nova_senha_confirmacao) {
        $mensagem_erro = "As senhas não coincidem.";
    } elseif (!empty($nova_senha) && strlen($nova_senha) < 6) {
        $mensagem_erro = "A senha precisa ter pelo menos 6 caracteres.";
    } elseif ($nova_foto['error'] === UPLOAD_ERR_OK) {
        // Validar upload de foto (apenas se o usuário enviou uma nova foto)
        $extensao = pathinfo($nova_foto['name'], PATHINFO_EXTENSION);
        $extensoes_validas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($extensao), $extensoes_validas)) {
            $mensagem_erro = "Formato de imagem inválido. Somente JPG, JPEG, PNG ou GIF são permitidos.";
        } else {
            // Definir novo nome para a imagem (para evitar conflitos)
            $novo_nome_foto = uniqid() . '.' . $extensao;
            move_uploaded_file($nova_foto['tmp_name'], '../img/' . $novo_nome_foto);
        }
    }

    // Se não houver erro, realizar a atualização no banco
    if (empty($mensagem_erro)) {
        // Atualizar dados do usuário no banco
        $query = 'UPDATE usuario SET nome = ?, email = ?';
        $parametros = [$novo_nome, $novo_email];

        // Atualizar a senha se foi fornecida
        if (!empty($nova_senha)) {
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT); // Criptografar a senha
            $query .= ', senha = ?';
            $parametros[] = $nova_senha_hash;
        }

        // Atualizar a foto de perfil, se houver
        if (!empty($novo_nome_foto)) {
            $query .= ', foto_perfil = ?';
            $parametros[] = $novo_nome_foto;
        }

        $query .= ' WHERE id_usuario = ?';
        $parametros[] = $userId;

        // Preparar e executar a query
        $stmt = $pdo->prepare($query);
        $stmt->execute($parametros);

        $mensagem_sucesso = "Perfil atualizado com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
    <div class="main-container">
        <h1>Editar Perfil</h1>

        <?php if ($mensagem_erro): ?>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

        <?php if ($mensagem_sucesso): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form">
            <div class="form-group">
                <label for="foto_perfil">Foto de Perfil</label>
                <input type="file" name="foto_perfil" id="foto_perfil">
                <img src="../img/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil Atual" class="foto-preview">
            </div>
            
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Nova Senha (opcional)</label>
                <input type="password" name="senha" id="senha">
            </div>

            <div class="form-group">
                <label for="senha_confirmacao">Confirmar Nova Senha (opcional)</label>
                <input type="password" name="senha_confirmacao" id="senha_confirmacao">
            </div>

            <button type="submit">Salvar Alterações</button>
        </form>

        <a class="link-button "href="perfil.php">Voltar para o Perfil</a>
    </div>
</body>
</html>
