<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        throw new Exception('Erro de validação de segurança (CSRF Token inválido). Ação bloqueada.');
    }
    // Opcional: Regenera o token após o uso para segurança extra
    // unset($_SESSION['csrf_token']); // Removido para permitir múltiplas requisições AJAX na mesma página
    return true;
}
?>