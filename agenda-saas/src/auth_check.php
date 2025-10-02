<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não há user_id na sessão, redireciona para o login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=' . urlencode('Por favor, faça o login para acessar.'));
    exit();
}

// O usuário admin (ID 1) tem acesso irrestrito e nunca expira.
if ($_SESSION['user_id'] == 1) {
    return; // Encerra o script e permite o acesso.
}

// Para todos os outros usuários, verifica a validade da assinatura no banco de dados.
require_once __DIR__ . '/database.php';
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT subscription_expires_at FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_expired = true; // Assume que expirou por padrão
if ($user && !empty($user['subscription_expires_at'])) {
    try {
        $expiration_date = new DateTime($user['subscription_expires_at']);
        $now = new DateTime();
        if ($expiration_date > $now) {
            $is_expired = false;
        }
    } catch (Exception $e) {
        // Lida com o caso de data inválida no banco, por segurança.
        $is_expired = true;
    }
}

// Se a assinatura expirou, destrói a sessão e redireciona.
if ($is_expired) {
    // Limpa todas as variáveis da sessão
    $_SESSION = array();

    // Destrói a sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    header('Location: login.php?error=' . urlencode('Sua assinatura expirou. Por favor, contate o suporte para renovar seu acesso.'));
    exit();
}
?>