<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Mensagens de erro e sucesso
$mensagem_erro = '';
$mensagem_sucesso = '';

// Estabelece a conexão
$pdo = pdo();  // Agora armazenamos o retorno da função pdo() em uma variável

// Recupera o token da URL
if (!isset($_GET['token'])) {
    $mensagem_erro = 'Token inválido.';
} else {
    $token = $_GET['token'];

    // Verifica se o token existe no banco e se ainda é válido
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $token_info = $stmt->fetch();

    if (!$token_info) {
        $mensagem_erro = 'Token inválido ou expirado.';
    }

    // Se o token for válido, o formulário de alteração de senha será exibido
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($mensagem_erro)) {
        $nova_senha = $_POST['nova_senha'];
        $nova_senha_confirmacao = $_POST['nova_senha_confirmacao'];

        // Validar as senhas
        if (empty($nova_senha) || empty($nova_senha_confirmacao)) {
            $mensagem_erro = 'Por favor, preencha ambas as senhas.';
        } elseif ($nova_senha !== $nova_senha_confirmacao) {
            $mensagem_erro = 'As senhas não coincidem.';
        } elseif (strlen($nova_senha) < 6) {
            $mensagem_erro = 'A senha precisa ter pelo menos 6 caracteres.';
        } else {
            // Atualiza a senha no banco de dados
            $user_id = $token_info['user_id']; // Pega o ID do usuário associado ao token
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

            // Atualiza a senha do usuário
            $stmt = $pdo->prepare('UPDATE usuario SET senha = ? WHERE id_usuario = ?');
            $stmt->execute([$senha_hash, $user_id]);

            // Invalida o token após a alteração
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);

            $mensagem_sucesso = 'Sua senha foi alterada com sucesso! Você será redirecionado para a página de login.';
            
            // Redirecionar para a página de login após a alteração de senha
            header('Refresh: 3; url=login.php');  // Redireciona após 3 segundos
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
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(180deg, #4B0082, #B43BF0);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: "Poppins", sans-serif;
        }

        .main-container {
            background: #fff;
            width: 420px;
            padding: 40px 50px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.2);
        }

        .main-container h1 {
            font-size: 28px;
            color: #2E004F;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #333;
            font-weight: 500;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        .form-group input:focus {
            border-color: #8A2BE2;
        }

        button {
            margin-top: 10px;
            background: #2E004F;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #55007F;
        }

        .link-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
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

        .error {
            background: #ffd4d4;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 10px;
            color: #a30000;
            font-size: 14px;
        }

        .success {
            background: #d4ffd8;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 10px;
            color: #1d7a2d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1>Redefinir Senha</h1>

        <!-- Exibindo mensagens de erro ou sucesso -->
        <?php if ($mensagem_erro): ?>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

        <?php if ($mensagem_sucesso): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" name="nova_senha" id="nova_senha" required>
                </div>

                <div class="form-group">
                    <label for="nova_senha_confirmacao">Confirmar Nova Senha</label>
                    <input type="password" name="nova_senha_confirmacao" id="nova_senha_confirmacao" required>
                </div>

                <button type="submit">Alterar Senha</button>
            </form>
        <?php endif; ?>

        <a class="link-button" href="../dashboard.php">Voltar</a>
    </div>
</body>
</html>