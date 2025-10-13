<?php
// bd/auth.php — helpers de sessão com proteção contra funções já declaradas em conexao.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna o id do usuário logado independente da chave usada pelo login.
 * Só define se ainda não existir (evita "Cannot redeclare").
 */
if (!function_exists('current_user_id')) {
    function current_user_id() {
        return $_SESSION['id_usuario']
            ?? $_SESSION['user_id']
            ?? ($_SESSION['usuario']['id_usuario'] ?? null)
            ?? ($_SESSION['user']['id_usuario'] ?? null)
            ?? ($_SESSION['auth']['id_usuario'] ?? null);
    }
}

/** True se houver usuário logado */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return current_user_id() !== null;
    }
}

/** True se admin */
if (!function_exists('is_admin')) {
    function is_admin() {
        $role = $_SESSION['user_role'] ?? $_SESSION['tipo_usuario'] ?? ($_SESSION['usuario']['tipo_usuario'] ?? null);
        return $role === 'admin';
    }
}