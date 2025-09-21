<?php
session_start();
require_once("../bd/conexao.php");

// 🔐 Verifica se o usuário logado é admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem gerenciar locais.");
}

$pdo = pdo();
$msg = "";

// CREATE
if (isset($_POST['create'])) {
    $nome        = $_POST['nome'];
    $tipo        = $_POST['tipo'];
    $regiao      = $_POST['regiao'];
    $faixa_preco = $_POST['faixa_preco'];
    $servicos    = $_POST['servicos'];

    $sql = "INSERT INTO locais (nome, tipo, regiao, faixa_preco, servicos) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $tipo, $regiao, $faixa_preco, $servicos]);

    $msg = "<div class='success'>Local adicionado com sucesso!</div>";
}

// UPDATE
if (isset($_POST['update'])) {
    $id          = $_POST['id_local'];
    $nome        = $_POST['nome'];
    $tipo        = $_POST['tipo'];
    $regiao      = $_POST['regiao'];
    $faixa_preco = $_POST['faixa_preco'];
    $servicos    = $_POST['servicos'];

    $sql = "UPDATE locais 
            SET nome=?, tipo=?, regiao=?, faixa_preco=?, servicos=? 
            WHERE id_local=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $tipo, $regiao, $faixa_preco, $servicos, $id]);

    $msg = "<div class='success'>Local atualizado!</div>";
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

    <!-- Container esquerdo -->
    <div class="left-container-crud">
        <!-- Container create -->
        <div class="container-create">
            <h2>Cadastro dos locais</h2>
            <form method="POST" class="form-crud">
                <input type="text" name="nome" placeholder="Nome" class="half" required>

                <select name="tipo" class="half" required>
                    <option value="Restaurante">Restaurante</option>
                    <option value="Bar">Bar</option>
                    <option value="Parque">Parque</option>
                    <option value="Evento">Evento</option>
                    <option value="Museu">Museu</option>
                    <option value="Outro">Outro</option>
                </select>

                <input type="text" name="regiao" placeholder="Região" class="half" required>

                <select name="faixa_preco" class="half" required>
                    <option value="Econômico">Econômico</option>
                    <option value="Médio">Médio</option>
                    <option value="Alto">Alto</option>
                </select>

                <textarea name="servicos" placeholder="Serviços separados por vírgula" class="full"></textarea>

                <button type="submit" name="create" class="btn btn-edit full">Adicionar</button>
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
                    <th>Preço</th><th>Serviços</th><th>Ações</th>
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
                        <!-- Form de edição completo -->
                        <form method="POST" class="form-crud">
                            <input type="hidden" name="id_local" value="<?= $row['id_local'] ?>">

                            <input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" class="half" placeholder="Nome" required>

                            <select name="tipo" class="half" required>
                                <option value="Restaurante" <?= $row['tipo'] === 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                                <option value="Bar" <?= $row['tipo'] === 'Bar' ? 'selected' : '' ?>>Bar</option>
                                <option value="Parque" <?= $row['tipo'] === 'Parque' ? 'selected' : '' ?>>Parque</option>
                                <option value="Evento" <?= $row['tipo'] === 'Evento' ? 'selected' : '' ?>>Evento</option>
                                <option value="Museu" <?= $row['tipo'] === 'Museu' ? 'selected' : '' ?>>Museu</option>
                                <option value="Outro" <?= $row['tipo'] === 'Outro' ? 'selected' : '' ?>>Outro</option>
                            </select>

                            <input type="text" name="regiao" value="<?= htmlspecialchars($row['regiao']) ?>" class="half" placeholder="Região" required>

                            <select name="faixa_preco" class="half" required>
                                <option value="Econômico" <?= $row['faixa_preco'] === 'Econômico' ? 'selected' : '' ?>>Econômico</option>
                                <option value="Médio" <?= $row['faixa_preco'] === 'Médio' ? 'selected' : '' ?>>Médio</option>
                                <option value="Alto" <?= $row['faixa_preco'] === 'Alto' ? 'selected' : '' ?>>Alto</option>
                            </select>

                            <input type="text" name="servicos" value="<?= htmlspecialchars($row['servicos']) ?>" class="full" placeholder="Serviços">

                            <button type="submit" name="update" class="btn btn-edit half">Editar</button>
                        </form>
                        <a href="crud.php?delete=<?= $row['id_local'] ?>" class="btn btn-delete half" onclick="return confirm('Excluir mesmo?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
