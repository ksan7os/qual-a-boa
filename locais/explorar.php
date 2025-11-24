<?php
// locais/explorar.php

require __DIR__ . '/../bd/conexao.php';
$pdo = pdo();
if (!$pdo) {
    die("Erro de conexão com o banco de dados.");
}

// ====== Filtros ======
$tipo        = $_GET['tipo']        ?? '';
$faixa_preco = $_GET['faixa_preco'] ?? '';
$endereco    = $_GET['endereco']    ?? '';
$nome        = $_GET['nome']        ?? '';
$ordenar     = $_GET['ordenar']     ?? 'nome';

// ====== Query base ======
$query = "
SELECT
  l.id_local,
  l.nome,
  l.tipo,
  l.endereco,
  l.faixa_preco,
  l.horario_funcionamento,
  l.servicos,
  l.imagem_capa,
  COALESCE(AVG(a.nota), 0) AS avaliacao_media,
  COUNT(a.id_avaliacao) AS total_avaliacoes,

  -- Cálculo de popularidade (modelo IMDb)
  (
    (COUNT(a.id_avaliacao) / (COUNT(a.id_avaliacao) + 50)) * COALESCE(AVG(a.nota), 0)
    +
    (50 / (COUNT(a.id_avaliacao) + 50)) * (SELECT AVG(nota) FROM avaliacoes)
  ) AS popularidade

FROM locais l
LEFT JOIN avaliacoes a ON a.id_local = l.id_local
WHERE 1=1
";

$params = [];

// ====== Aplicação dos filtros ======
if ($tipo !== '') {
    $query .= " AND l.tipo = :tipo";
    $params[':tipo'] = $tipo;
}
if ($faixa_preco !== '') {
    $query .= " AND l.faixa_preco = :faixa_preco";
    $params[':faixa_preco'] = $faixa_preco;
}
if ($endereco !== '') {
    $query .= " AND l.endereco LIKE :endereco";
    $params[':endereco'] = "%$endereco%";
}
if ($nome !== '') {
    $query .= " AND l.nome LIKE :nome";
    $params[':nome'] = "%$nome%";
}

$query .= " GROUP BY l.id_local ";

// ====== Ordenação ======
switch ($ordenar) {
    case 'avaliacao':
        $query .= " ORDER BY avaliacao_media DESC";
        break;
    case 'popularidade':
        $query .= " ORDER BY popularidade DESC";
        break;
    default:
        $query .= " ORDER BY l.nome ASC";
        break;
}

$stmt = $pdo->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ====== Tipos (ENUM ampliado) ======
$tipos = [
    'Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria','Pub','Balada',
    'Parque','Trilha','Praça','Museu','Teatro','Cinema','Show','Evento',
    'Feira','Mercado','Centro Cultural','Atração Turística','Outro'
];

// ====== Helper para exibir serviços ======
function split_list($str) {
    if (!$str) return [];
    $parts = array_map('trim', explode(',', (string)$str));
    return array_values(array_filter($parts, fn($v) => $v !== ''));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Explorar Locais</title>

    <style>
        /* ======================= RESET ======================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at bottom, #9c1fd4 0%, #000 60%);
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .page-wrapper {
            width: 1200px;
            max-width: 100%;
            color: #fff;
        }

        /* =============== TÍTULO =============== */
        .page-title {
            text-align: center;
            font-size: 42px;
            font-weight: 500;
            color: #e6b5ff;
            margin-bottom: 32px;
        }

        /* =============== FILTROS =============== */
        .filters-bar {
            background: #ffffff;
            border-radius: 999px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            margin: 0 auto 40px;
            max-width: 980px;
        }

        .filter-field {
            flex: 1;
            position: relative;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            border-radius: 999px;
            border: 1px solid #d9d9d9;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
            background: #ffffff;
            color: #555;
            appearance: none;
        }

        .filter-field.select-wrapper::after {
            content: "▾";
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #777;
            pointer-events: none;
        }

        .apply-button {
            border: none;
            border-radius: 999px;
            padding: 12px 32px;
            background: #3e0c7a;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.5);
            transition: 0.15s;
        }

        .apply-button:hover {
            background: #5210a0;
            transform: translateY(-1px);
        }

        /* =============== GRID =============== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
            justify-items: center;
        }

        /* =============== CARD =============== */
        .place-card {
            background: #ffffff;
            border-radius: 18px;
            width: 320px;
            max-width: 100%;
            overflow: hidden;
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.45);
            color: #111;
            display: flex;
            flex-direction: column;
        }

        .place-image {
            height: 150px;
            object-fit: cover;
            width: 100%;
        }

        .place-body {
            padding: 16px 18px 20px;
        }

        .place-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .place-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .tag-pill {
            background: #d3ddff;
            color: #3a3a3a;
            font-size: 12px;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 500;
        }

        .place-address {
            font-size: 12px;
            color: #444;
            line-height: 1.5;
            margin-bottom: 6px;
        }

        .place-hours {
            font-size: 12px;
            color: #444;
            margin-bottom: 8px;
        }

        .place-filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }

        .filter-chip {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #d3ddff;
            color: #3a3a3a;
        }

        /* Estrelas */
        .stars {
            position: relative;
            display: inline-block;
            font-size: 16px;
            line-height: 1;
        }

        .stars::before {
            content: "★★★★★";
            color: #d4d4d4;
        }

        .stars::after {
            content: "★★★★★";
            color: #f4c43b;
            position: absolute;
            top: 0;
            left: 0;
            width: var(--rating-pct);
            overflow: hidden;
        }

        .place-rating-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            margin-top: 12px;
            margin-bottom: 16px;
        }

        .details-button {
            display: block;
            width: 70%;
            margin: 0 auto;
            border-radius: 999px;
            padding: 12px 0;
            background: #757dff;
            color: white;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            transition: 0.15s;
            text-decoration: none;
            text-align: center;
        }

        .details-button:hover {
            background: #626bff;
            transform: translateY(-1px);
        }

        /* Responsivo */
        @media (max-width: 1050px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 750px) {
            .filters-bar {
                flex-wrap: wrap;
                border-radius: 24px;
            }
            .apply-button {
                width: 100%;
            }
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
        /* Voltar */
      .link-button {
        display: inline-block;
        margin-top: 60px;
        text-decoration: none;
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        backdrop-filter: blur(6px);
        transition: 0.2s ease;
      }

      .link-button:hover {
        background: rgba(255,255,255,0.2);
      }
    </style>
</head>

<body>
<div class="page-wrapper">

    <h1 class="page-title">Explorar locais</h1>

    <!-- ================= FILTROS ================= -->
    <form method="GET">
        <div class="filters-bar">

            <div class="filter-field">
                <input type="text" name="nome" class="filter-input"
                       placeholder="Nome do local"
                       value="<?= htmlspecialchars($nome) ?>">
            </div>

            <div class="filter-field select-wrapper">
                <select name="tipo" class="filter-select">
                    <option value="">Tipo</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t ?>" <?= $tipo === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field select-wrapper">
                <select name="faixa_preco" class="filter-select">
                    <option value="">Faixa de Preço</option>
                    <option value="Econômico" <?= $faixa_preco === "Econômico" ? "selected" : "" ?>>Econômico</option>
                    <option value="Médio" <?= $faixa_preco === "Médio" ? "selected" : "" ?>>Médio</option>
                    <option value="Alto" <?= $faixa_preco === "Alto" ? "selected" : "" ?>>Alto</option>
                </select>
            </div>

            <div class="filter-field">
                <input type="text" name="endereco" class="filter-input"
                       placeholder="Localização"
                       value="<?= htmlspecialchars($endereco) ?>">
            </div>

            <div class="filter-field select-wrapper">
                <select name="ordenar" class="filter-select" onchange="this.form.submit()">
                    <option value="nome" <?= $ordenar === 'nome' ? 'selected' : '' ?>>A–Z</option>
                    <option value="avaliacao" <?= $ordenar === 'avaliacao' ? 'selected' : '' ?>>Melhor avaliados</option>
                    <option value="popularidade" <?= $ordenar === 'popularidade' ? 'selected' : '' ?>>Mais populares</option>
                </select>
            </div>

            <button class="apply-button" type="submit">Aplicar</button>
        </div>
    </form>

    <!-- ================= GRID ================= -->
    <div class="cards-grid">
        <?php foreach ($locais as $local): ?>

            <?php
                $img = $local["imagem_capa"]
                    ? "../img/capa-locais/" . htmlspecialchars($local["imagem_capa"])
                    : "../img/default-profile.jpg";

                $nomeCard = htmlspecialchars($local["nome"]);
                $tipoCard = htmlspecialchars($local["tipo"]);
                $precoCard = htmlspecialchars($local["faixa_preco"]);
                $endCard = htmlspecialchars($local["endereco"]);
                $rating = (float)($local["avaliacao_media"] ?? 0);
                $ratingPct = ($rating / 5) * 100;
                $servicos = split_list($local["servicos"]);
                $preview = array_slice($servicos, 0, 3);
                $extra = max(0, count($servicos) - 3);
            ?>

            <div class="place-card">
                <img src="<?= $img ?>" class="place-image">

                <div class="place-body">
                    <div class="place-name"><?= $nomeCard ?></div>

                    <div class="place-tags">
                        <span class="tag-pill"><?= $tipoCard ?></span>
                        <span class="tag-pill"><?= $precoCard ?></span>
                    </div>

                    <?php if ($endCard): ?>
                        <div class="place-address">📍 <?= $endCard ?></div>
                    <?php endif; ?>

                    <?php if ($local["horario_funcionamento"]): ?>
                        <div class="place-hours">🕒 <?= htmlspecialchars($local["horario_funcionamento"]) ?></div>
                    <?php endif; ?>

                    <?php if ($preview): ?>
                        <div class="place-filters-row">
                            <?php foreach ($preview as $s): ?>
                                <span class="filter-chip"><?= htmlspecialchars($s) ?></span>
                            <?php endforeach; ?>

                            <?php if ($extra > 0): ?>
                                <span class="filter-chip">+<?= $extra ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="place-rating-row">
                        <div class="stars" style="--rating-pct: <?= $ratingPct ?>%;"></div>
                        <span class="rating-value"><?= number_format($rating, 1) ?></span>
                    </div>

                    <a href="detalhes.php?id=<?= $local['id_local'] ?>" class="details-button">
                        Ver detalhes
                    </a>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <a class="link-button" href="<?= url('./dashboard.php') ?>">← Voltar para o menu</a>
</div>
</body>
</html>