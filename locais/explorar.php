<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Obter a instância do PDO
$pdo = pdo();  // Aqui estamos chamando a função pdo() para obter a conexão

// Verificar se a conexão foi estabelecida
if (!$pdo) {
    die("Erro de conexão com o banco de dados.");
}

// O resto do código segue normalmente
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$faixa_preco = isset($_GET['faixa_preco']) ? $_GET['faixa_preco'] : '';
$regiao = isset($_GET['regiao']) ? $_GET['regiao'] : '';
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';

// Iniciar a consulta SQL com os filtros
$query = "SELECT * FROM locais WHERE 1";

// Adicionar filtros à consulta
if ($tipo) {
    $query .= " AND tipo = :tipo";
}
if ($faixa_preco) {
    $query .= " AND faixa_preco = :faixa_preco";
}
if ($regiao) {
    $query .= " AND regiao = :regiao";
}
if ($nome) {
    $query .= " AND nome LIKE :nome";
}

$stmt = $pdo->prepare($query);

// Vincula os parâmetros dos filtros à consulta
if ($tipo) {
    $stmt->bindParam(':tipo', $tipo);
}
if ($faixa_preco) {
    $stmt->bindParam(':faixa_preco', $faixa_preco);
}
if ($regiao) {
    $stmt->bindParam(':regiao', $regiao);
}
if ($nome) {
    $nome = "%$nome%";  // Usar LIKE no nome para pesquisa
    $stmt->bindParam(':nome', $nome);
}

$stmt->execute();

// Buscar os locais
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Explorar Locais</title>
    <link rel="stylesheet" href="../css/explorar.css">
</head>
<body>
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <input type="text" name="nome" placeholder="Nome do local" value="<?= htmlspecialchars($nome) ?>">

            <select name="tipo">
                <option value="">Tipo</option>
                <option value="Restaurante" <?= $tipo == 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                <option value="Bar" <?= $tipo == 'Bar' ? 'selected' : '' ?>>Bar</option>
                <option value="Parque" <?= $tipo == 'Parque' ? 'selected' : '' ?>>Parque</option>
                <option value="Evento" <?= $tipo == 'Evento' ? 'selected' : '' ?>>Evento</option>
                <option value="Museu" <?= $tipo == 'Museu' ? 'selected' : '' ?>>Museu</option>
                <option value="Outro" <?= $tipo == 'Outro' ? 'selected' : '' ?>>Outro</option>
            </select>

            <select name="faixa_preco">
                <option value="">Faixa de Preço</option>
                <option value="Econômico" <?= $faixa_preco == 'Econômico' ? 'selected' : '' ?>>Econômico</option>
                <option value="Médio" <?= $faixa_preco == 'Médio' ? 'selected' : '' ?>>Médio</option>
                <option value="Alto" <?= $faixa_preco == 'Alto' ? 'selected' : '' ?>>Alto</option>
            </select>

            <input type="text" name="regiao" placeholder="Região" value="<?= htmlspecialchars($regiao) ?>">

            <button type="submit">Aplicar Filtros</button>
        </form>
    </div>

    <div class="locais-container">
        <?php foreach ($locais as $local): ?>
            <div class="local-card">
                <img src="../img/capa-locais/<?= htmlspecialchars($local['imagem_capa']) ?>" alt="Imagem de capa" class="local-image">
                <h3><?= htmlspecialchars($local['nome']) ?></h3>
                <p><?= htmlspecialchars($local['tipo']) ?></p>
                <p><?= htmlspecialchars($local['regiao']) ?></p>
                <p>Faixa de Preço: <?= htmlspecialchars($local['faixa_preco']) ?></p>
                <p>Avaliação Média: <?= number_format($local['avaliacao_media'], 1) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <a class="link-button" href="<?= url('./dashboard.php') ?>">Voltar para o menu</a>
</body>
</html>
