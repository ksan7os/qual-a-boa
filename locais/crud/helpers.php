<?php
// helpers.php — funções comuns do CRUD
session_start();
require_once("../../bd/conexao.php"); // usa sua função pdo()

function pdo_conn() {
  // sua conexao.php expõe pdo(); se for diferente, ajuste aqui.
  return pdo();
}

function require_admin() {
  if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("Acesso negado. Apenas administradores.");
  }
}

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function check_csrf($token) {
  if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$token)) {
    die("CSRF inválido.");
  }
}

function sanitize_str($s) {
  return trim((string)($s ?? ''));
}

function validate_local($data, &$errors) {
  $required = ['nome','tipo','endereco','faixa_preco'];
  foreach ($required as $r) {
    if (empty($data[$r])) $errors[$r] = "Campo obrigatório.";
  }
  if (!empty($data['site']) && !filter_var($data['site'], FILTER_VALIDATE_URL)) {
    $errors['site'] = "URL inválida.";
  }
  if (!empty($data['email_contato']) && !filter_var($data['email_contato'], FILTER_VALIDATE_EMAIL)) {
    $errors['email_contato'] = "E-mail inválido.";
  }
  // telefone mínimo 10 dígitos (removendo não dígitos)
  if (!empty($data['telefone'])) {
    $digits = preg_replace('/\D/', '', $data['telefone']);
    if (strlen($digits) < 10) $errors['telefone'] = "Telefone inválido.";
  }
  return empty($errors);
}

function handle_upload_imagem($field = 'imagem_capa') {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
    return null; // sem upload
  }
  $allowed = ['jpg','jpeg','png','webp'];
  $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed)) {
    return ['error' => "Formato de imagem inválido (use jpg, jpeg, png, webp)."];
  }
  if ($_FILES[$field]['size'] > 3*1024*1024) {
    return ['error' => "Imagem maior que 3MB."];
  }
  $nome = uniqid('img_') . "." . $ext;
  $dest = "../../img/capa-locais/" . $nome; // caminho real a partir de /locais/crud/
  if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
    return ['error' => "Falha ao salvar imagem no servidor."];
  }
  return ['filename' => $nome];
}

function tipos_locais() {
  return [
    'Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria','Pub','Balada',
    'Parque','Trilha','Praça','Museu','Teatro','Cinema','Show','Evento',
    'Feira','Mercado','Centro Cultural','Atração Turística','Outro'
  ];
}

function faixas_preco() {
  return ['Econômico','Médio','Alto'];
}

// flash message simples via GET (?ok=1&msg=xxx)
function flash($key) {
  return isset($_GET[$key]) ? htmlspecialchars($_GET[$key]) : null;
}