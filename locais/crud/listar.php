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
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="container-crud">
  <div class="row" style="justify-content: space-between; align-items:center;">
    <h2>Locais</h2>
    <a class="btn btn-edit" href="adicionar.php">+ Novo Local</a>
  </div>

  <?php if ($msg = flash('msg')): ?>
    <div class="success" style="margin: 10px 0;"><?= $msg ?></div>
  <?php endif; ?>

  <form method="GET" class="form-crud" style="margin: 10px 0;">
    <input class="half" type="text" name="q" placeholder="Buscar por nome, tipo, endereço ou serviços..." value="<?= htmlspecialchars($q) ?>">

    <!-- NOVO: seletor de ordenação -->
    <select name="ordenar" class="half" onchange="this.form.submit()">
      <option value="id_local" <?= $ordenar == 'id_local' ? 'selected' : '' ?>>Mais recentes</option>
      <option value="avaliacao" <?= $ordenar == 'avaliacao' ? 'selected' : '' ?>>Melhor avaliados</option>
      <option value="popularidade" <?= $ordenar == 'popularidade' ? 'selected' : '' ?>>Mais populares</option>
    </select>

    <button class="btn" type="submit">Buscar</button>
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

  <div style="margin-top:12px;">
    <?php for ($p=1; $p<=$pages; $p++): ?>
      <?php if ($p == $page): ?>
        <strong>[<?= $p ?>]</strong>
      <?php else: ?>
        <a href="?q=<?= urlencode($q) ?>&ordenar=<?= urlencode($ordenar) ?>&page=<?= $p ?>">[<?= $p ?>]</a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <p style="margin-top:20px;"><a class="link-button" href="../explorar.php">← Explorar locais</a></p>
</div>
</body>
</html>
