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

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber os dados do formulário
    $novo_nome = trim($_POST['nome']);
    $novo_email = trim($_POST['email']);
    $nova_senha = $_POST['senha'];
    $nova_senha_confirmacao = $_POST['senha_confirmacao'];
    $nova_foto = $_FILES['foto_perfil'];  // Recebe o arquivo da foto de perfil

    // Validar os dados
    if (empty($novo_nome) || empty($novo_email)) {
        $mensagem_erro = "Nome e E-mail são obrigatórios.";
    } elseif (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "E-mail inválido.";
    } elseif (!empty($nova_senha) && $nova_senha !== $nova_senha_confirmacao) {
        $mensagem_erro = "As senhas não coincidem.";
    } elseif (!empty($nova_senha) && strlen($nova_senha) < 6) {
        $mensagem_erro = "A senha precisa ter pelo menos 6 caracteres.";
    } else {
        // Se o botão de "Remover Imagem" for clicado, atualiza a foto para a padrão
        if (isset($_POST['remover_imagem'])) {
            // Atualiza a foto de perfil para a imagem padrão (default)
            $novo_nome_foto = 'default-profile.jpg';
        } elseif ($nova_foto['error'] === UPLOAD_ERR_OK) {
            // Se o usuário enviou uma nova foto
            $extensao = pathinfo($nova_foto['name'], PATHINFO_EXTENSION);
            $extensoes_validas = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($extensao), $extensoes_validas)) {
                $mensagem_erro = "Formato de imagem inválido. Somente JPG, JPEG, PNG ou GIF são permitidos.";
            } else {
                // Gerar um nome único para a imagem (para evitar conflitos de nomes)
                $novo_nome_foto = uniqid('foto_') . '.' . $extensao;
                // Movendo o arquivo para o diretório de imagens
                $caminho_destino = '../img/' . $novo_nome_foto;
                if (move_uploaded_file($nova_foto['tmp_name'], $caminho_destino)) {
                    // Sucesso no upload da imagem
                } else {
                    $mensagem_erro = "Erro ao salvar a imagem. Tente novamente.";
                }
            }
        } else {
            // Se não houver upload, manter a foto atual
            $novo_nome_foto = $foto_perfil;  // Mantém a foto atual se não for alterada
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

            // Atualizar a foto de perfil, se houver (se não foi removida)
            if (!empty($novo_nome_foto)) {
                $query .= ', foto_perfil = ?';
                $parametros[] = $novo_nome_foto;
            }

            $query .= ' WHERE id_usuario = ?';
            $parametros[] = $userId;

            // Preparar e executar a query
            $stmt = $pdo->prepare($query);
            $stmt->execute($parametros);

            // Redirecionar para o perfil
            header('Location: perfil.php');
            exit;
        }
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
    <script>
        // Função para atualizar a imagem de prévia quando o usuário selecionar uma nova imagem
        function atualizarImagemPrevia(input) {
            var file = input.files[0];  // Pega o primeiro arquivo selecionado
            var reader = new FileReader();  // Cria o leitor de arquivos

            reader.onload = function(e) {
                // Altera a fonte da imagem de prévia para o arquivo carregado
                document.getElementById('foto_preview').src = e.target.result;
                // Atualiza o campo oculto para o novo arquivo
                document.getElementById('foto_perfil').setAttribute('data-foto', e.target.result);
            }

            // Se um arquivo foi selecionado, o FileReader vai ler o arquivo
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Função para remover a imagem e resetar o preview para a imagem padrão
        function removerImagem() {
            // Atualiza o preview para a imagem padrão
            document.getElementById('foto_preview').src = "../img/default-profile.jpg";
            // Limpa o campo de upload de imagem
            document.getElementById('foto_perfil').value = "";
            // Atualiza o campo oculto para "default-profile.jpg"
            document.getElementById('foto_perfil').setAttribute('data-foto', 'default-profile.jpg');
        }
    </script>
    <style>
        body {
            background: linear-gradient(180deg, #4B0082, #B43BF0);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: "Poppins", sans-serif;
            color: #333;
        }

        .main-container {
            background: #fff;
            width: 80%;
            max-width: 960px;
            padding: 40px 50px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
            margin-top: 50px;
        }

        h1 {
            font-size: 26px;
            color: #2E004F;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: 0.2s;
        }

        .form-group input:focus {
            border-color: #A63CE9;
        }

        .foto-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        button {
            padding: 12px 20px;
            background: #A63CE9;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        button:hover {
            background: #8A2BE2;
        }

        .link-button {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            border-radius: 30px;
            border: 2px solid #A63CE9;
            color: #A63CE9;
            text-decoration: none;
            font-size: 16px;
            background: transparent;
            transition: background 0.3s, color 0.3s;
        }

        .link-button:hover {
            background: #A63CE9;
            color: #fff;
        }

        /* Estilo para os botões de alterar foto */
        .foto-preview + button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1>Editar Perfil</h1>

        <?php if ($mensagem_erro): ?>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form">
            <!-- Campo oculto para enviar a imagem (se removida ou carregada) -->
            <input type="hidden" name="foto_perfil" id="foto_perfil" value="<?php echo htmlspecialchars($foto_perfil); ?>" data-foto="<?php echo htmlspecialchars($foto_perfil); ?>">

            <div class="form-group">
                <label for="foto_perfil">Foto de Perfil</label>
                <input type="file" name="foto_perfil" id="foto_perfil" onchange="atualizarImagemPrevia(this)">
                <img src="../img/<?php echo htmlspecialchars($foto_perfil); ?>" id="foto_preview" alt="Foto de Perfil Atual" class="foto-preview">
            </div>

            <!-- Botão de remover imagem -->
            <button type="button" onclick="removerImagem()">Remover Foto de Perfil</button>

            <!-- Restante do formulário -->
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

            <button type="submit">Salvar as alterações</button>
        </form>
        <a class="link-button" href="perfil.php">Salvar e Voltar para o Perfil</a>
    </div>
</body>
</html>
