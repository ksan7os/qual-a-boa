<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Mensagens de erro e sucesso
$mensagem_erro = '';
$mensagem_sucesso = '';

// Estabelece a conexão
$pdo = pdo();  // Agora armazenamos o retorno da função pdo() em uma variável

// Processamento do formulário de recuperação de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega o e-mail fornecido
    $email = trim($_POST['email']);
    
    // Verifica se o e-mail é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = 'Por favor, insira um e-mail válido.';
    } else {
        // Verifica se o e-mail existe no banco de dados
        $stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Gerar token único
            $token = bin2hex(random_bytes(50));  // Gera um token aleatório
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Token expira em 1 hora

            // Armazenar o token no banco de dados
            $stmt = $pdo->prepare('
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$user['id_usuario'], $token, $expires_at]);

            // Enviar o link para o e-mail do usuário
            $link_recuperacao = "https://seusite.com/redefinir-senha.php?token=$token";
            $assunto = 'Recuperação de Senha';
            $mensagem = "Clique no link abaixo para redefinir sua senha:\n$link_recuperacao";
            $headers = "From: no-reply@seusite.com\r\n";
            $headers .= "Reply-To: no-reply@seusite.com\r\n";
            $headers .= "Content-type: text/html\r\n"; // Para enviar o e-mail em HTML

            if (mail($email, $assunto, $mensagem, $headers)) {
                $mensagem_sucesso = "E-mail enviado com sucesso. Verifique sua caixa de entrada.";
            } else {
                $mensagem_erro = "Houve um erro ao enviar o e-mail. Tente novamente.";
            }
        } else {
            $mensagem_erro = 'Este e-mail não está cadastrado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="main-container">
        <h1>Recuperação de Senha</h1>

        <?php if ($mensagem_erro): ?>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

        <?php if ($mensagem_sucesso): ?>
            <div class="success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="email">Informe seu e-mail:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <button type="submit">Enviar Link de Recuperação</button>
        </form>

        <a class="link-button "href="../dashboard.php">Voltar</a>
    </div>
</body>
</html>
