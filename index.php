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
      top: 55px;
      right: 70px;
      display: flex;
      gap: 26px;
      z-index: 5;
    }
    .pill-btn {
      background: rgba(0,0,0,.35);
      border: 2px solid rgba(138,46,210,.8);
      border-radius: 999px;
      padding: 8px 46px 9px;
      color: #fff;
      font-size: .7rem;
      text-decoration: none;
      box-shadow: 0 0 14px rgba(138,46,210,.45);
      transition: transform .12s ease, box-shadow .12s ease;
    }
    .pill-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 0 20px rgba(138,46,210,.9);
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
  <div class="landing">
    <div class="top-actions">
      <a class="pill-btn" href="<?= url('auth/login.php') ?>">Login</a>
      <a class="pill-btn" href="<?= url('auth/register.php') ?>">Cadastre-se</a>
    </div>
    <div class="hero-text">
      <h1 class="hero-title">Qual a Boa?</h1>
      <p class="hero-sub">
        Descubra bares,<br>
        restaurantes e<br>
        <span class="accent">eventos perto de<br> você.</span>
      </p>
    </div>
    <div class="hero-image-wrapper">
      <img src="/img/ponte.png" alt="Ponte neon" class="hero-img">
    </div>
  </div>
</body>
</html>
