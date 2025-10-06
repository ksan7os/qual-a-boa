<?php
// conexao.php
declare(strict_types=1);

const BASE_PATH = '/php/qual-a-boa';

function url(string $path): string {
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

// Config DB
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306'; // <- Adicione a porta aqui
const DB_NAME = 'qualaboa';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

// Conexão PDO
function pdo(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    return $pdo;
}

// Sessão segura (básico)
function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        ]);
        session_start();
    }
}

function is_logged_in(): bool {
    start_session();
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . url('auth/login.php'));
        exit;
    }
}

// Flash messages
function set_flash(string $key, string $msg): void {
    start_session();
    $_SESSION['flash'][$key] = $msg;
}
function get_flash(string $key): ?string {
    start_session();
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
