<?php
// Incluir a conexão com o banco de dados
require __DIR__ . '/../bd/conexao.php';

// Obter a instância do PDO
$pdo = pdo();  // Aqui estamos chamando a função pdo() para obter a conexão

// Verificar se a conexão foi estabelecida
if (!$pdo) {
    die("Erro de conexão com o banco de dados.");
}

// 1. Validação do parâmetro 'id'
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h3>Local inválido.</h3>";
    exit;
}

$id_local = (int) $_GET['id'];

// 2. Consulta ao banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM locais WHERE id_local = :id");
    $stmt->bindParam(':id', $id_local, PDO::PARAM_INT);
    $stmt->execute();
    $local = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$local) {
        echo "<h3>Local não encontrado.</h3>";
        exit;
    }
} catch (PDOException $e) {
    echo "<h3>Erro ao carregar informações do local.</h3>";
    exit;
}

// 3. Preparar dados para exibição
$nome          = htmlspecialchars($local['nome']);
$tipo          = htmlspecialchars($local['tipo']);
$regiao        = htmlspecialchars($local['regiao']);
$faixa_preco   = htmlspecialchars($local['faixa_preco']);
$servicos      = nl2br(htmlspecialchars($local['servicos']));
$avaliacao     = number_format($local['avaliacao_media'], 1);
$imagem_capa = !empty($local['imagem_capa'])
    ? "../img/capa-locais/" . htmlspecialchars($local['imagem_capa'])
    : "../img/default-profile.jpg";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Detalhes do Local - <?= $nome ?></title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="detalhes-container">
  <img src="<?= $imagem_capa ?>" alt="Imagem de <?= $nome ?>">

  <h2><?= $nome ?></h2>
  <p><strong>Tipo:</strong> <?= $tipo ?></p>
  <p><strong>Região:</strong> <?= $regiao ?></p>
  <p><strong>Faixa de Preço:</strong> <?= $faixa_preco ?></p>
  <p><strong>Serviços:</strong><br> <?= $servicos ?></p>
  <p><strong>Avaliação Média:</strong> <?= $avaliacao ?>/5</p>

  <a href="explorar.php" class="btn-voltar">← Voltar para Explorar</a>
</div>

</body>
</html>
