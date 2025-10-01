<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($name)) {
        die('O nome do cliente é obrigatório.');
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO clientes (user_id, name, phone, email, notes) VALUES (?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$user_id, $name, $phone, $email, $notes]);
        header('Location: clients.php?message=Cliente adicionado com sucesso!');
        exit();
    } catch (PDOException $e) {
        die("Erro ao adicionar cliente: " . $e->getMessage());
    }
} else {
    header('Location: add_client.php');
    exit();
}
?>