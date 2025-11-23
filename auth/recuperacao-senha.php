<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';

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
            // Verifica se o usuário já tem um token válido
            $stmt = $pdo->prepare('SELECT id FROM password_resets WHERE user_id = ? AND expires_at > NOW()');
            $stmt->execute([$user['id_usuario']]);
            $existing_token = $stmt->fetch();

            // Gerar token único
            $token = bin2hex(random_bytes(50));  // Gera um token aleatório
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Token expira em 1 hora

            if ($existing_token) {
                // Se já houver um token válido, atualiza a expiração do token
                $stmt = $pdo->prepare('UPDATE password_resets SET token = ?, expires_at = ? WHERE id = ?');
                $stmt->execute([$token, $expires_at, $existing_token['id']]);
            } else {
                // Se não houver token, cria um novo
                $stmt = $pdo->prepare('
                    INSERT INTO password_resets (user_id, token, expires_at)
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([$user['id_usuario'], $token, $expires_at]);
            }

            // Enviar o link para o e-mail do usuário usando o PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kauansan7os@gmail.com';  // Seu e-mail
            $mail->Password = 'dpis ngdo jkqz rhim';            // Sua senha do e-mail (preferencialmente use um app password)
            $mail->setFrom('kauansan7os@gmail.com', 'Qual a Boa?');
            $mail->addAddress($email);

            // Definir assunto e corpo do e-mail
            $link_recuperacao = "http://localhost/php/qual-a-boa/auth/redefinir-senha.php?token=$token";
            $mail->Subject = 'Recuperação de Senha';
            $mail->Subject = 'Recuperação de Senha';

            $mail->Body = '
                <html>
                <body>
                    <p>Olá,</p>
                    <p>Você solicitou a recuperação da sua senha em nossa plataforma. Para redefinir sua senha, clique no link abaixo:</p>
                    <p><a href="' . $link_recuperacao . '">Clique aqui para redefinir sua senha</a></p>
                    <p>Se você não solicitou essa mudança, pode ignorar este e-mail.</p>
                    <p>Atenciosamente,</p>
                    <p><strong>Qual a Boa?</strong></p>
                </body>
                </html>
            ';
            $mail->IsHTML(true);  // Necessário para enviar o e-mail em formato HTML

            // Enviar e-mail
            if (!$mail->send()) {
                $mensagem_erro = 'Erro ao enviar o e-mail: ' . $mail->ErrorInfo;
            } else {
                $mensagem_sucesso = 'E-mail enviado com sucesso. Verifique sua caixa de entrada.';
            }
        } else {
            $mensagem_erro = 'Este e-mail não está cadastrado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
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
  <div class="forgot-wrapper">
    <h1>Esqueceu a senha?</h1>
    <p class="subtext">
      Informe o email de cadastro para receber o<br>
      link de recuperação de senha.
    </p>

        <!-- Exibindo mensagens de erro ou sucesso -->
        <?php if ($mensagem_erro): ?>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro); ?></div>
        <?php endif; ?>

    <?php if ($mensagem_sucesso): ?>
      <div class="success"><?= htmlspecialchars($mensagem_sucesso) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <input
          type="email"
          name="email"
          id="email"
          class="form-control"
          placeholder="Email"
          required
        >
      </div>

      <button type="submit" class="btn-primary">Enviar</button>
    </form>

        <a class="link-button" href="../dashboard.php">Voltar</a>
    </div>
</body>
</html>