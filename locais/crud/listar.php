<?php
require_once("helpers.php");
require_admin();

$pdo = pdo_conn();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE nome LIKE :q OR tipo LIKE :q OR endereco LIKE :q OR servicos LIKE :q";
  $params[':q'] = "%$q%";
}

$total = $pdo->prepare("SELECT COUNT(*) FROM locais $where");
$total->execute($params);
$rows = (int)$total->fetchColumn();

$sql = "SELECT id_local, nome, tipo, endereco, faixa_preco, horario_funcionamento, site, telefone, email_contato, servicos, imagem_capa
        FROM locais
        $where
        ORDER BY id_local DESC
        LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
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
        <a href="?q=<?= urlencode($q) ?>&page=<?= $p ?>">[<?= $p ?>]</a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <p style="margin-top:20px;"><a class="link-button" href="../explorar.php">← Explorar locais</a></p>
</div>
</body>
</html>
