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
  <title>Qual a Boa?</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">

  <style>
    :root {
      --roxo: #8A2ED2;
      --preto: #000;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
    }
    body {
      min-height: 100vh;
      background: var(--preto);
      color: #fff;
    }
    .landing {
      position: relative;
      width: min(1180px, 100%);
      height: 100vh;
      margin: 0 auto;
      overflow: hidden;
    }
    .top-actions {
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
    .hero-text {
      position: absolute;
      left: 120px;
      top: 210px;
      max-width: 360px;
      z-index: 3;
    }
    .hero-title {
      font-size: clamp(3.4rem, 5vw, 4rem);
      font-weight: 600;
      color: var(--roxo);
      margin-bottom: 20px;
      letter-spacing: -.02em;
      line-height: .95;
    }
    .hero-sub {
      font-size: 1.3rem;
      line-height: 1.25;
    }
    .hero-sub .accent { color: var(--roxo); }
    .hero-image-wrapper {
      position: absolute;
      right: -140px;
      bottom: -25px;
      width: 720px;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      pointer-events: none;
    }
    .hero-img {
      width: 100%;
      height: auto;
      object-fit: contain;
      display: block;
      filter: drop-shadow(0 0 35px rgba(138,46,210,.3));
    }
  </style>
</head>

<body>
  <header class="top-buttons">
    <a href="<?= url('auth/login.php') ?>" class="btn-outline">Login</a>
    <a href="<?= url('auth/register.php') ?>" class="btn-outline">Cadastre-se</a>
  </header>

    <div class="hero-text">
      <h1 class="hero-title">Qual a Boa?</h1>
      <p class="hero-sub">
        Descubra bares,<br>
        restaurantes e<br>
        <span class="accent">eventos perto de<br> você.</span>
      </p>
    </div>

    <div class="hero-image-wrapper">
      <img src="/" alt="Ponte neon" class="hero-img">
    </div>

  </section>

</body>
</html>