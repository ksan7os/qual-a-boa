<?php
// locais/explorar.php

// Conexão
require __DIR__ . '/../bd/conexao.php';
$pdo = pdo();
if (!$pdo) {
    die("Erro de conexão com o banco de dados.");
}

// Filtros
$tipo        = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$faixa_preco = isset($_GET['faixa_preco']) ? $_GET['faixa_preco'] : '';
$endereco    = isset($_GET['endereco']) ? $_GET['endereco'] : '';
$nome        = isset($_GET['nome']) ? $_GET['nome'] : '';

// Consulta base: média calculada ao vivo pela tabela avaliacoes
$query = "SELECT
            l.id_local,
            l.nome,
            l.tipo,
            l.endereco,
            l.faixa_preco,
            COALESCE(AVG(a.nota), 0) AS avaliacao_media,
            l.imagem_capa,
            l.horario_funcionamento,
            l.servicos
          FROM locais l
          LEFT JOIN avaliacoes a ON a.id_local = l.id_local
          WHERE 1=1";
$params = [];

// Filtros opcionais
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

$query .= " GROUP BY l.id_local
            ORDER BY l.nome ASC";

$stmt = $pdo->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tipos (ENUM ampliado)
$tipos = [
    'Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria','Pub','Balada',
    'Parque','Trilha','Praça','Museu','Teatro','Cinema','Show','Evento',
    'Feira','Mercado','Centro Cultural','Atração Turística','Outro'
];

// Helper rápido
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
    <link rel="stylesheet" href="../css/explorar.css">
</head>
<body>
    <!-- Filtros -->
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <input type="text" name="nome" placeholder="Nome do local" value="<?= htmlspecialchars($nome) ?>">

            <select name="tipo">
                <option value="">Tipo</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $tipo === $t ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="faixa_preco">
                <option value="">Faixa de Preço</option>
                <option value="Econômico" <?= $faixa_preco == 'Econômico' ? 'selected' : '' ?>>Econômico</option>
                <option value="Médio"      <?= $faixa_preco == 'Médio'      ? 'selected' : '' ?>>Médio</option>
                <option value="Alto"       <?= $faixa_preco == 'Alto'       ? 'selected' : '' ?>>Alto</option>
            </select>

            <input type="text" name="endereco" placeholder="Localização (endereço, bairro, rua...)" value="<?= htmlspecialchars($endereco) ?>">

            <button type="submit">Aplicar Filtros</button>
        </form>
    </div>

    <!-- Grid de locais -->
    <div class="locais-container">
        <?php foreach ($locais as $local): ?>
            <?php
              // Dados do card
              $img = $local['imagem_capa']
                     ? "../img/capa-locais/" . htmlspecialchars($local['imagem_capa'])
                     : "../img/default-profile.jpg";

              $nomeCard = htmlspecialchars($local['nome']);
              $tipoCard = htmlspecialchars($local['tipo']);
              $preco    = htmlspecialchars($local['faixa_preco']);
              $end      = htmlspecialchars($local['endereco'] ?? '');

              $rating = (float)($local['avaliacao_media'] ?? 0);
              $rating = max(0, min(5, $rating));
              $ratingPct = ($rating/5)*100;

              // Primeiro trecho do horário
              $horario_snippet = '';
              if (!empty($local['horario_funcionamento'])) {
                $parts = preg_split('/[;\n]+/', (string)$local['horario_funcionamento']);
                $horario_snippet = trim($parts[0] ?? '');
              }

              // Serviços: 3 primeiros + “+N”
              $servicos_arr = split_list($local['servicos'] ?? '');
              $serv_preview = array_slice($servicos_arr, 0, 3);
              $serv_extra   = max(0, count($servicos_arr) - 3);
            ?>
            <div class="local-card">
              <img src="<?= $img ?>" alt="Imagem de <?= $nomeCard ?>" class="local-image">

              <div class="local-body">
                <h3 class="local-title"><?= $nomeCard ?></h3>

                <div class="badges-row">
                  <span class="badge badge-type"><?= $tipoCard ?></span>
                  <span class="badge badge-preco"><?= $preco ?></span>
                </div>

                <?php if ($end): ?>
                  <p class="local-address"><span class="icon">📍</span> <?= $end ?></p>
                <?php endif; ?>

                <?php if ($horario_snippet): ?>
                  <p class="local-hours"><span class="icon">🕒</span> <?= htmlspecialchars($horario_snippet) ?></p>
                <?php endif; ?>

                <?php if (!empty($serv_preview)): ?>
                  <div class="chips">
                    <?php foreach ($serv_preview as $s): ?>
                      <span class="chip"><?= htmlspecialchars($s) ?></span>
                    <?php endforeach; ?>
                    <?php if ($serv_extra > 0): ?>
                      <span class="chip chip-more">+<?= $serv_extra ?></span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <div class="rating-row">
                  <div class="stars" style="--rating-pct: <?= $ratingPct ?>%;" aria-label="Avaliação <?= number_format($rating,1) ?> de 5"></div>
                  <span class="rating-number"><?= number_format($rating,1) ?></span>
                </div>

                <a href="detalhes.php?id=<?= (int)$local['id_local'] ?>" class="btn-detalhes">Ver detalhes</a>
              </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a class="link-button" href="<?= url('./dashboard.php') ?>">Voltar para o menu</a>
</body>
</html>