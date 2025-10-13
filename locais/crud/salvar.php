<?php
require_once("helpers.php");
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Método inválido"); }
check_csrf($_POST['csrf'] ?? '');

$pdo = pdo_conn();

$data = [
  'id_local' => (isset($_POST['id_local']) && is_numeric($_POST['id_local'])) ? (int)$_POST['id_local'] : null,
  'nome' => sanitize_str($_POST['nome'] ?? ''),
  'tipo' => sanitize_str($_POST['tipo'] ?? ''),
  'descricao' => sanitize_str($_POST['descricao'] ?? ''),
  'endereco' => sanitize_str($_POST['endereco'] ?? ''),
  'faixa_preco' => sanitize_str($_POST['faixa_preco'] ?? ''),
  'horario_funcionamento' => sanitize_str($_POST['horario_funcionamento'] ?? ''),
  'site' => sanitize_str($_POST['site'] ?? ''),
  'telefone' => sanitize_str($_POST['telefone'] ?? ''),
  'email_contato' => sanitize_str($_POST['email_contato'] ?? ''),
  'redes_sociais' => sanitize_str($_POST['redes_sociais'] ?? ''),
  'servicos' => sanitize_str($_POST['servicos'] ?? ''),
  'imagem_capa_atual' => sanitize_str($_POST['imagem_capa_atual'] ?? '')
];

$errors = [];
if (!validate_local($data, $errors)) {
  $msg = "Erros no formulário: " . implode(", ", array_keys($errors));
  $back = $data['id_local'] ? "editar.php?id=".$data['id_local'] : "adicionar.php";
  header("Location: $back?msg=" . urlencode($msg));
  exit;
}

// upload opcional
$upload = handle_upload_imagem('imagem_capa');
if (is_array($upload) && isset($upload['error'])) {
  $msg = $upload['error'];
  $back = $data['id_local'] ? "editar.php?id=".$data['id_local'] : "adicionar.php";
  header("Location: $back?msg=" . urlencode($msg));
  exit;
}
$imagem_final = $data['imagem_capa_atual'];
if (is_array($upload) && isset($upload['filename'])) {
  $imagem_final = $upload['filename'];
}

if ($data['id_local']) {
  // UPDATE
  $sql = "UPDATE locais SET
    nome=?, tipo=?, descricao=?, endereco=?, faixa_preco=?, horario_funcionamento=?,
    site=?, telefone=?, email_contato=?, redes_sociais=?, servicos=?, imagem_capa=?
    WHERE id_local=?";
  $params = [
    $data['nome'],$data['tipo'],$data['descricao'],$data['endereco'],$data['faixa_preco'],
    $data['horario_funcionamento'],$data['site'],$data['telefone'],$data['email_contato'],
    $data['redes_sociais'],$data['servicos'],$imagem_final,$data['id_local']
  ];
} else {
  // INSERT
  $sql = "INSERT INTO locais
  (nome, tipo, descricao, endereco, faixa_preco, horario_funcionamento,
   site, telefone, email_contato, redes_sociais, servicos, imagem_capa)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
  $params = [
    $data['nome'],$data['tipo'],$data['descricao'],$data['endereco'],$data['faixa_preco'],
    $data['horario_funcionamento'],$data['site'],$data['telefone'],$data['email_contato'],
    $data['redes_sociais'],$data['servicos'],$imagem_final
  ];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header("Location: listar.php?ok=1&msg=" . urlencode("Local salvo com sucesso!"));
exit;
