<?php
session_start();
require_once("../bd/conexao.php");

// Verifica se o usuário logado é admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem gerenciar locais.");
}

$pdo = pdo();
$msg = "";

// Variáveis para o formulário de edição
$nome = $tipo = $regiao = $faixa_preco = $servicos = $imagem_capa = "";

// Verifica se o parâmetro 'editar' existe na URL (para atualizar)
if (isset($_GET['editar'])) {
    $id_local = $_GET['editar'];

    // Busca os dados do local no banco de dados
    $sql = "SELECT * FROM locais WHERE id_local = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_local]);
    $local = $stmt->fetch(PDO::FETCH_ASSOC);

    // Preenche os campos do formulário com os dados do local
    if ($local) {
        $nome = $local['nome'];
        $tipo = $local['tipo'];
        $regiao = $local['regiao'];
        $faixa_preco = $local['faixa_preco'];
        $servicos = $local['servicos'];
        $imagem_capa = $local['imagem_capa'];  // Para exibir a imagem atual
    } else {
        $msg = "<div class='error'>Local não encontrado!</div>";
    }
} else {
    // Caso não esteja editando, os campos ficam vazios
    $nome = $tipo = $regiao = $faixa_preco = $servicos = $imagem_capa = "";
}

// CREATE ou UPDATE
if (isset($_POST['submit'])) {
    $nome        = $_POST['nome'];
    $tipo        = $_POST['tipo'];
    $regiao      = $_POST['regiao'];
    $faixa_preco = $_POST['faixa_preco'];
    $servicos    = $_POST['servicos'];
    $id_local    = isset($_POST['id_local']) ? $_POST['id_local'] : null;

    // Processa a imagem de capa
    $imagem_capa = isset($_POST['imagem_capa_atual']) ? $_POST['imagem_capa_atual'] : null;
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        // Caminho da pasta para salvar a imagem
        $upload_dir = "../img/capa-locais/"; // Caminho da pasta
        // Gera um nome único para a imagem
        $imagem_capa = uniqid('img_') . '.' . pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION);
        $upload_file = $upload_dir . $imagem_capa;
        
        // Move a imagem para a pasta
        if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $upload_file)) {
            // Imagem salva com sucesso
        } else {
            $msg = "<div class='error'>Erro ao fazer o upload da imagem.</div>";
        }
    }

    if ($id_local) {
        // UPDATE: Atualiza o local existente
        $sql = "UPDATE locais 
                SET nome=?, tipo=?, regiao=?, faixa_preco=?, servicos=?, imagem_capa=? 
                WHERE id_local=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $tipo, $regiao, $faixa_preco, $servicos, $imagem_capa, $id_local]);

        $msg = "<div class='success'>Local atualizado!</div>";

        // Após atualização, reiniciamos a operação para criar um novo local
        $nome = $tipo = $regiao = $faixa_preco = $servicos = $imagem_capa = "";  // Limpa os campos
        $id_local = null;  // Reseta o id_local para null, voltando para a criação de novos locais
    } else {
        // CREATE: Cria um novo local
        $sql = "INSERT INTO locais (nome, tipo, regiao, faixa_preco, servicos, imagem_capa) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $tipo, $regiao, $faixa_preco, $servicos, $imagem_capa]);

        $msg = "<div class='success'>Local adicionado com sucesso!</div>";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM locais WHERE id_local=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $msg = "<div class='error'>Local excluído!</div>";
}

// READ
$result = $pdo->query("SELECT * FROM locais");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Locais</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container-crud">
    <!-- Container esquerdo (formulário de criação e edição) -->
    <div class="left-container-crud">
        <!-- Container create -->
        <div class="container-create">
            <h2>Locais</h2>
            <form method="POST" enctype="multipart/form-data" class="form-crud">
                <!-- Campo oculto para enviar o ID do local, se for uma atualização -->
                <?php if (isset($_GET['editar'])): ?>
                    <input type="hidden" name="id_local" value="<?= htmlspecialchars($local['id_local']) ?>">
                <?php endif; ?>

                <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" placeholder="Nome" class="half" required>

                <select name="tipo" class="half" required>
                    <option value="Restaurante" <?= $tipo === 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                    <option value="Bar" <?= $tipo === 'Bar' ? 'selected' : '' ?>>Bar</option>
                    <option value="Parque" <?= $tipo === 'Parque' ? 'selected' : '' ?>>Parque</option>
                    <option value="Evento" <?= $tipo === 'Evento' ? 'selected' : '' ?>>Evento</option>
                    <option value="Museu" <?= $tipo === 'Museu' ? 'selected' : '' ?>>Museu</option>
                    <option value="Outro" <?= $tipo === 'Outro' ? 'selected' : '' ?>>Outro</option>
                </select>

                <input type="text" name="regiao" value="<?= htmlspecialchars($regiao) ?>" placeholder="Região" class="half" required>

                <select name="faixa_preco" class="half" required>
                    <option value="Econômico" <?= $faixa_preco === 'Econômico' ? 'selected' : '' ?>>Econômico</option>
                    <option value="Médio" <?= $faixa_preco === 'Médio' ? 'selected' : '' ?>>Médio</option>
                    <option value="Alto" <?= $faixa_preco === 'Alto' ? 'selected' : '' ?>>Alto</option>
                </select>

                <textarea name="servicos" placeholder="Serviços separados por vírgula" class="full"><?= htmlspecialchars($servicos) ?></textarea>

                <!-- Exibição da imagem atual -->
                <?php if ($imagem_capa): ?>
                    <img src="../img/capa-locais/<?= htmlspecialchars($imagem_capa) ?>" alt="Imagem de capa" width="100">
                <?php else: ?>
                    <p>Sem imagem de capa</p>
                <?php endif; ?>

                <!-- Campo para upload de nova imagem -->
                <input type="file" name="imagem_capa" accept="image/*" class="half">

                <input type="hidden" name="imagem_capa_atual" value="<?= htmlspecialchars($imagem_capa) ?>">

                <button type="submit" name="submit" class="btn btn-edit full">Salvar</button>
            </form>
        </div>

        <!-- Botão logout -->
        <a href="../dashboard.php" class="link-button" style="margin: 50px 0; text-align: center; width: 100%;">Menu Principal</a>
    </div>

    <!-- Container direito (Listagem dos locais + ações) -->
    <div class="right-container-crud">
        <h2>Listagem dos locais</h2>
        <div class="table-wrapper">
            <table class="table-crud">
                <tr>
                    <th>ID</th><th>Nome</th><th>Tipo</th><th>Região</th>
                    <th>Preço</th><th>Serviços</th><th>Imagem de Capa</th><th>Ações</th>
                </tr>
                <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_local']) ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars($row['tipo']) ?></td>
                    <td><?= htmlspecialchars($row['regiao']) ?></td>
                    <td><?= htmlspecialchars($row['faixa_preco']) ?></td>
                    <td><?= htmlspecialchars($row['servicos']) ?></td>
                    <td>
                        <?php if ($row['imagem_capa']): ?>
                            <img src="../img/capa-locais/<?= htmlspecialchars($row['imagem_capa']) ?>" alt="Imagem de capa" width="100">
                        <?php else: ?>
                            <p>Sem imagem</p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Botão de Editar -->
                        <a href="crud.php?editar=<?= $row['id_local'] ?>" class="btn btn-edit">Editar</a>
                        <!-- Botão de Excluir -->
                        <a href="crud.php?delete=<?= $row['id_local'] ?>" class="btn btn-delete" onclick="return confirm('Excluir mesmo?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>