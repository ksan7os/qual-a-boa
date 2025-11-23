<?php
require __DIR__ . '/bd/conexao.php';
start_session();

if (is_logged_in()) {
    header('Location: ' . url('dashboard.php'));
    exit;
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Qual a Boa? - Início</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">

  <style>
    body { background: #000; }

    .top-buttons {
      position: absolute;
      top: 50px;
      right: 60px;
      display: flex;
      gap: 20px;
    }

    .btn-outline {
      padding: 10px 26px;
      border: 2px solid #A63CE9;
      border-radius: 30px;
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      transition: .2s;
    }

    .btn-outline:hover {
      background: #A63CE9;
    }

    .home-wrapper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 80px;
      height: 100vh;
    }

    .home-text h1 span {
      font-size: 92px;
      font-weight: 700;
      color: #A63CE9;
    }

    .home-text p {
      font-size: 56px;
      margin-top: 20px;
      color: #fff;
      line-height: 1.3;
    }

    .home-text .purple {
      color: #A63CE9;
    }

    .home-image img {
      width: 1300px;
      margin-top: 50px;
    }
  </style>
</head>

<body>
  <header class="top-buttons">
    <a href="<?= url('auth/login.php') ?>" class="btn-outline">Login</a>
    <a href="<?= url('auth/register.php') ?>" class="btn-outline">Cadastre-se</a>
  </header>

  <section class="home-wrapper">
    
    <div class="home-text">
      <h1><span>Qual a Boa?</span></h1>

      <p>
        Descubra bares, <br>
        restaurantes e <br>
        <span class="purple">eventos perto de você.</span>
      </p>
    </div>

    <div class="home-image">
      <img src="img/ponte.png" alt="Ponte iluminada">
    </div>

  </section>

</body>
</html>