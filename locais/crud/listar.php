<?php
require_once("helpers.php");
require_admin();

$pdo = pdo_conn();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// ===== NOVO: Tipo de ordenação =====
$ordenar = $_GET['ordenar'] ?? 'id_local';
$orderBy = match($ordenar) {
  'avaliacao' => 'media_avaliacao DESC',
  'popularidade' => 'popularidade DESC',
  default => 'id_local DESC'
};

// ===== Filtros de busca =====
$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE l.nome LIKE :q OR l.tipo LIKE :q OR l.endereco LIKE :q OR l.servicos LIKE :q";
  $params[':q'] = "%$q%";
}

// ===== Contagem total de locais =====
$total = $pdo->prepare("SELECT COUNT(*) FROM locais l $where");
$total->execute($params);
$rows = (int)$total->fetchColumn();

// ===== Consulta principal com cálculo de popularidade =====
$sql = "
SELECT 
  l.id_local,
  l.nome,
  l.tipo,
  l.endereco,
  l.faixa_preco,
  l.horario_funcionamento,
  l.site,
  l.telefone,
  l.email_contato,
  l.servicos,
  l.imagem_capa,

  IFNULL(AVG(a.nota), 0) AS media_avaliacao,
  COUNT(a.id_avaliacao) AS total_avaliacoes,

  -- Cálculo de popularidade baseado no modelo IMDb
  (
    (COUNT(a.id_avaliacao) / (COUNT(a.id_avaliacao) + 50)) * IFNULL(AVG(a.nota), 0)
    +
    (50 / (COUNT(a.id_avaliacao) + 50)) * (SELECT AVG(nota) FROM avaliacoes)
  ) AS popularidade

FROM locais l
LEFT JOIN avaliacoes a ON l.id_local = a.id_local
$where
GROUP BY l.id_local
ORDER BY $orderBy
LIMIT :lim OFFSET :off
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pages = max(1, ceil($rows / $per_page));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Locais (CRUD)</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      background: linear-gradient(180deg, #4B0082, #B43BF0);
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      font-family: "Poppins", sans-serif;
      color: #333;
    }

    .container-crud {
      background: #fff;
      width: 80%;
      max-width: 2000px;
      padding: 40px 50px;
      border-radius: 20px;
      box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
      margin-top: 50px;
    }

    h2 {
      font-size: 26px;
      color: #2E004F;
      margin-bottom: 20px;
    }

    .btn {
      background: #A63CE9;
      color: #fff;
      padding: 12px 20px;
      border-radius: 30px;
      font-size: 16px;
      text-decoration: none;
      margin-bottom: 20px;
      transition: 0.3s;
    }

    .btn:hover {
      background: #8A2BE2;
    }

    .table-wrapper {
      margin-top: 20px;
      border-radius: 12px;
      overflow-x: auto;
    }

    .table-crud {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .table-crud th, .table-crud td {
      padding: 12px 18px;
      text-align: left;
      border: 1px solid #e5e7eb;
    }

    .table-crud th {
      background-color: #A63CE9;
      color: #fff;
    }

    .table-crud td {
      background: #fff;
    }

    .btn-edit, .btn-delete {
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 14px;
      text-decoration: none;
      margin: 0 5px;
      transition: 0.3s;
    }

    .btn-edit {
      background-color: #4CAF50;
      color: #fff;
    }

    .btn-edit:hover {
      background-color: #45a049;
    }

    .btn-delete {
      background-color: #f44336;
      color: #fff;
    }

    .btn-delete:hover {
      background-color: #e53935;
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .pagination a {
      text-decoration: none;
      color: #A63CE9;
      font-size: 16px;
      font-weight: bold;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #A63CE9;
      transition: 0.3s;
    }

    .pagination a:hover {
      background-color: #A63CE9;
      color: white;
    }

    .pagination strong {
      color: #A63CE9;
    }

    /* Link "Explorar locais" */
    .link-button {
      display: inline-block;
      background: transparent;
      color: #A63CE9;
      padding: 8px 12px;
      border-radius: 30px;
      font-size: 16px;
      text-decoration: none;
      margin-top: 20px;
      transition: background 0.3s, color 0.3s;
    }

    .link-button:hover {
      background: #A63CE9;
      color: #fff;
    }

    /* Estilo da numeração da tabela */
    .pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 20px;
    }

    .pagination a {
      text-decoration: none;
      color: #A63CE9;
      font-size: 16px;
      font-weight: bold;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #A63CE9;
      transition: 0.3s;
    }

    .pagination a:hover {
      background-color: #A63CE9;
      color: white;
    }

    .pagination strong {
      color: #A63CE9;
    }

    /* Estilo para o input do formulário de busca */
    .form-crud {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 10px;
    }

    .form-crud input {
      width: 65%;
      padding: 10px 15px;
      font-size: 16px;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
    }

    .form-crud select {
      padding: 10px 15px;
      font-size: 16px;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      width: 30%;
    }

    .form-crud button {
      padding: 10px 20px;
      background: #A63CE9;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .form-crud button:hover {
      background: #8A2BE2;
    }
  </style>
</head>
<body>
<div class="container-crud">
  <div class="row" style="justify-content: space-between; align-items:center;">
    <h2>Locais</h2>
    <a class="btn" href="adicionar.php">+ Novo Local</a>
  </div>

  <?php if ($msg = flash('msg')): ?>
    <div class="success" style="margin: 10px 0;"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Formulário de busca -->
  <form method="GET" class="form-crud">
    <input class="half" type="text" name="q" placeholder="Buscar por nome, tipo, endereço ou serviços..." value="<?= htmlspecialchars($q) ?>">
    <select name="ordenar" class="half" onchange="this.form.submit()">
      <option value="id_local" <?= $ordenar == 'id_local' ? 'selected' : '' ?>>Mais recentes</option>
      <option value="avaliacao" <?= $ordenar == 'avaliacao' ? 'selected' : '' ?>>Melhor avaliados</option>
      <option value="popularidade" <?= $ordenar == 'popularidade' ? 'selected' : '' ?>>Mais populares</option>
    </select>
    <button type="submit">Buscar</button>
  </form>

  <div class="table-wrapper">
    <table class="table-crud">
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Tipo</th>
        <th>Localização</th>
        <th>Preço</th>
        <th>Avaliação Média</th>
        <th>Total Avaliações</th>
        <th>Popularidade</th>
        <th>Horário</th>
        <th>Site</th>
        <th>Número</th>
        <th>E-mail</th>
        <th>Serviços</th>
        <th>Imagem</th>
        <th>Ações</th>
      </tr>
      <?php foreach ($locais as $row): ?>
        <tr>
          <td><?= (int)$row['id_local'] ?></td>
          <td><?= htmlspecialchars($row['nome']) ?></td>
          <td><?= htmlspecialchars($row['tipo']) ?></td>
          <td><?= htmlspecialchars($row['endereco']) ?></td>
          <td><?= htmlspecialchars($row['faixa_preco']) ?></td>
          <td><?= number_format($row['media_avaliacao'], 2) ?></td>
          <td><?= (int)$row['total_avaliacoes'] ?></td>
          <td><?= number_format($row['popularidade'], 2) ?></td>
          <td><?= htmlspecialchars($row['horario_funcionamento']) ?></td>
          <td>
            <?php if (!empty($row['site'])): ?>
              <a href="<?= htmlspecialchars($row['site']) ?>" target="_blank" rel="noopener noreferrer">Abrir</a>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['telefone']) ?></td>
          <td><?= htmlspecialchars($row['email_contato']) ?></td>
          <td><?= htmlspecialchars($row['servicos']) ?></td>
          <td>
            <?php if (!empty($row['imagem_capa'])): ?>
              <img src="../../img/capa-locais/<?= htmlspecialchars($row['imagem_capa']) ?>" width="60" style="border-radius:6px;">
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <a class="btn btn-edit" href="editar.php?id=<?= (int)$row['id_local'] ?>">Editar</a>
            <a class="btn btn-delete" href="excluir.php?id=<?= (int)$row['id_local'] ?>" onclick="return confirm('Excluir mesmo?')">Excluir</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Paginação -->
  <div class="pagination">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php if ($p == $page): ?>
        <strong>[<?= $p ?>]</strong>
      <?php else: ?>
        <a href="?q=<?= urlencode($q) ?>&ordenar=<?= urlencode($ordenar) ?>&page=<?= $p ?>">[<?= $p ?>]</a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <p style="margin-top:20px;">
    <a class="link-button" href="../explorar.php">← Explorar locais</a>
  </p>
</div>
</body>
</html>