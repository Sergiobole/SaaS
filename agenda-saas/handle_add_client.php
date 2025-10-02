<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';
require_once 'src/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($name)) {
        // Em um app real, redirecionar com uma mensagem de erro
        die('O nome do cliente é obrigatório.');
    }

    // Lida com o upload da foto
    $photo_path = handle_photo_upload($_FILES['photo'], 'client_photos');

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO clientes (user_id, name, phone, email, notes, photo_path) VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    try {
        $stmt->execute([$user_id, $name, $phone, $email, $notes, $photo_path]);
        header('Location: clients.php?message=Cliente adicionado com sucesso!');
        exit();
    } catch (PDOException $e) {
        // Em um app real, logar o erro e redirecionar com mensagem amigável
        error_log("Erro ao adicionar cliente: " . $e->getMessage());
        header('Location: add_client.php?error=Erro ao salvar no banco de dados.');
        exit();
    }
} else {
    header('Location: add_client.php');
    exit();
}
?>