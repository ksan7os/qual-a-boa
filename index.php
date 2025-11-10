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
      font-family:  'DM Sans', sans-serif;
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
    font-family: 'DM Sans', sans-serif;
    font-weight: 300; /* Light */
    font-size: 70px;
    color: transparent; /* Texto transparente para mostrar apenas o gradiente */
    background: linear-gradient(90deg, #8A2BE2, #FF69B4); /* Degradê de roxo para rosa */
    background-clip: text; /* Aplica o background apenas ao texto */
    -webkit-background-clip: text; /* Para compatibilidade com navegadores WebKit */
    margin-bottom: 20px;
    letter-spacing: -.02em;
    line-height: .95;

    /* Propriedades para manter o texto em uma única linha */
    white-space: nowrap; /* Impede quebras de linha */
    overflow: visible; /* Permite que o texto ultrapasse o contêiner se necessário */
    display: inline-block; /* Alternativa: pode usar "display: block" dependendo do layout */
    width: auto; /* Permite que a largura se ajuste ao conteúdo */
}
   .hero-sub {
    font-family: 'DM Sans', sans-serif;
    font-size: 48px; /* Atualizado de 1.3rem para 64px conforme solicitado */
    font-weight: 300; /* Light weight */
    line-height: 1.25; /* Mantido do código original */
    color: white; /* Assumindo que o texto é branco como na imagem */
    white-space: nowrap; /* Para garantir que fique em uma única linha */
    overflow: visible;
    width: auto;
}

/* Para a parte destacada em roxo */
.hero-sub .highlight {
    color: #c027ff; /* Cor roxa para destacar parte do texto */
}
    .hero-image-wrapper {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 50%;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 1;
}

.hero-img {
    width: 100%;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 0 35px rgba(138,46,210,.3));
    display: block;
    max-height: 80vh;
}
    /* Certifique-se de que o contêiner pai tem position relative */
    .hero-container {
        position: relative;
    }

/* Certifique-se de que o contêiner pai tenha position relative */
    .hero-container {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 100vh; /* Altura mínima para garantir espaço suficiente */
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
        <span class="highlight">eventos perto de<br> você.</span>
      </p>
    </div>

    <div class="hero-image-wrapper">
      <img src="ponte.png" alt="Ponte neon" class="hero-img">
    </div>
  </div>
</body>
</html>