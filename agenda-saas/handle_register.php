<?php
session_start();
require_once 'src/csrf.php';
require_once 'src/database.php'; // Para a conexão principal do app (saas_agenda)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Coleta de dados do formulário
$name = $_POST['name'] ?? null;
$business_name = $_POST['business_name'] ?? null;
$cpf_cnpj = $_POST['cpf_cnpj'] ?? null;
$phone = $_POST['phone'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$user_token = $_POST['access_token'] ?? null;

// Validação básica
if (empty($name) || empty($business_name) || empty($email) || empty($password) || empty($user_token)) {
    header('Location: register.php?error=' . urlencode('Por favor, preencha todos os campos obrigatórios.'));
    exit();
}

// Validação do CSRF Token (APÓS a validação dos campos)
validate_csrf_token($_POST['csrf_token'] ?? '');

$token_id = null;
$token_is_valid = false;

// 1. Validar o Token de Acesso no DB principal `saas_agenda`
try {
    $pdo_app = getDbConnection(); // Usando a conexão principal
    
    // Pega o token específico informado pelo usuário
    $stmt = $pdo_app->prepare("SELECT id, token, created_at, duration_days, is_used FROM access_tokens WHERE token = ?");
    $stmt->execute([$user_token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($token_data && !$token_data['is_used']) {
        // Token encontrado e não utilizado, agora checa a validade
        $created_at = new DateTime($token_data['created_at']);
        $duration_days = (int)$token_data['duration_days'];
        $expiry_date = $created_at->add(new DateInterval("P{$duration_days}D"));
        $current_date = new DateTime();

        if ($current_date < $expiry_date) {
            $token_is_valid = true;
            $token_id = $token_data['id'];
        }
    }
} catch (Exception $e) {
    // Adiciona o erro na URL para depuração (opcional, remova em produção)
    header('Location: register.php?error=' . urlencode('Ocorreu um erro ao validar o token de acesso: ' . $e->getMessage()));
    exit();
}

if (!$token_is_valid) {
    header('Location: register.php?error=' . urlencode('Token de acesso inválido, expirado ou já utilizado.'));
    exit();
}

// 2. Se o token é válido, prosseguir com o cadastro no DB `saas_agenda`
try {
    $pdo_app->beginTransaction();

    // Validar se o email já existe no app
    $stmt = $pdo_app->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Este e-mail já está cadastrado em outra conta.');
    }

    // Criar o novo usuário
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $expires_at = (new DateTime())->add(new DateInterval("P30D"))->format('Y-m-d H:i:s'); // Assinatura de 30 dias

    $stmt = $pdo_app->prepare(
        "INSERT INTO usuarios (name, business_name, email, password, cpf_cnpj, phone, subscription_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $business_name, $email, $hashed_password, $cpf_cnpj, $phone, $expires_at]);
    $new_user_id = $pdo_app->lastInsertId();

    $pdo_app->commit();

} catch (Exception $e) {
    if ($pdo_app->inTransaction()) {
        $pdo_app->rollBack();
    }
    header('Location: register.php?error=' . urlencode($e->getMessage()));
    exit();
}

// 3. Se o usuário foi criado, marcar o token como usado no DB `saas_agenda`
try {
    $stmt = $pdo_app->prepare("UPDATE access_tokens SET is_used = 1, used_at = NOW(), used_by_user_id = ? WHERE id = ?");
    $stmt->execute([$new_user_id, $token_id]);
} catch (Exception $e) {
    error_log("CRÍTICO: Usuário #{$new_user_id} criado, mas falha ao marcar token #{$token_id} como usado.");
}

// 4. Redirecionar para o login
header('Location: login.php?message=' . urlencode('Conta criada com sucesso! Sua assinatura de 30 dias está ativa. Faça o login.'));
exit();
?>