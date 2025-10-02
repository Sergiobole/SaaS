<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';
require_once 'src/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $_SESSION['user_id'];
    $existing_photo_path = $_POST['existing_photo_path'] ?? null;

    if (empty($name) || empty($id)) {
        die('Dados inválidos.');
    }

    // Lida com o upload da foto, passando o caminho da foto antiga para possível exclusão
    $photo_path = handle_photo_upload($_FILES['photo'], 'client_photos', $existing_photo_path);

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "UPDATE clientes SET name = ?, phone = ?, email = ?, notes = ?, photo_path = ? WHERE id = ? AND user_id = ?"
    );
    
    try {
        $stmt->execute([$name, $phone, $email, $notes, $photo_path, $id, $user_id]);
        header('Location: clients.php?message=Cliente atualizado com sucesso!');
        exit();
    } catch (PDOException $e) {
        error_log("Erro ao atualizar cliente: " . $e->getMessage());
        header('Location: edit_client.php?id=' . $id . '&error=Erro ao salvar no banco de dados.');
        exit();
    }
} else {
    header('Location: clients.php');
    exit();
}
?>