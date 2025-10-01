<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($name) || empty($id)) {
        die('Dados inválidos.');
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE clientes SET name = ?, phone = ?, email = ?, notes = ? WHERE id = ? AND user_id = ?");
    
    try {
        // A ordem dos parâmetros deve corresponder aos `?`
        $stmt->execute([$name, $phone, $email, $notes, $id, $user_id]);
        header('Location: clients.php?message=Cliente atualizado com sucesso!');
        exit();
    } catch (PDOException $e) {
        die("Erro ao atualizar cliente: " . $e->getMessage());
    }
} else {
    header('Location: clients.php');
    exit();
}
?>