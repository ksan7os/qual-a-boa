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
  <title>Esqueceu a senha? - Qual a Boa?</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #3c0a5d 0%, #7e1c8d 60%, #a72791 100%);
    }

    .forgot-wrapper {
      width: 500px;
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
      padding: 46px 46px 34px;
      text-align: center;
    }

    .forgot-wrapper h1 {
      font-size: 2.2rem;
      font-weight: 600;
      color: #3c064c;
      margin-bottom: 10px;
    }

    .forgot-wrapper p.subtext {
      font-size: 0.9rem;
      color: #e17272;
      margin-bottom: 26px;
      line-height: 1.4;
    }

    .form-group {
      margin-bottom: 18px;
      text-align: left;
    }

    .form-control {
      width: 100%;
      height: 52px;
      border: none;
      outline: none;
      border-radius: 999px;
      background: #fff;
      padding: 0 20px;
      font-size: 0.9rem;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
      width: 140px;
      height: 42px;
      border: none;
      background: #2f0035;
      color: #fff;
      border-radius: 999px;
      cursor: pointer;
      font-weight: 500;
      margin: 0 auto 18px;
      display: block;
      transition: transform .15s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
    }

    .helper-text {
      font-size: 0.78rem;
      color: #4b5563;
    }

    .helper-text a {
      color: #a72791;
      text-decoration: none;
      font-weight: 500;
    }

    .helper-text a:hover {
      text-decoration: underline;
    }

    /* Mensagens do PHP */
    .error, .success {
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 0.8rem;
      margin-bottom: 14px;
      text-align: left;
    }

    .error {
      background: #ffe6e6;
      border: 1px solid #ff9f9f;
      color: #a10000;
    }

    .success {
      background: #e8fff0;
      border: 1px solid #8ee0b0;
      color: #06663a;
    }

    @media (max-width: 540px) {
      .forgot-wrapper {
        width: 92%;
        padding: 40px 22px 30px;
      }
      .forgot-wrapper h1 {
        font-size: 2rem;
      }
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

    <?php if ($mensagem_erro): ?>
      <div class="error"><?= htmlspecialchars($mensagem_erro) ?></div>
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

    <p class="helper-text">
      Já tem uma conta?
      <a href="login.php">Entrar</a>
    </p>
  </div>
</body>
</html>