<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';
require_once 'src/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $user_id = $_SESSION['user_id'];
    $existing_photo_path = $_POST['existing_photo_path'] ?? null;

    if (empty($name) || is_null($price) || is_null($duration) || !$id) {
        header('Location: services.php?error=Dados inválidos.');
        exit();
    }

    // Lida com o upload da foto, passando o caminho da foto antiga para possível exclusão
    $photo_path = handle_photo_upload($_FILES['photo'], 'service_photos', $existing_photo_path);

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "UPDATE services SET name = ?, description = ?, price = ?, duration = ?, photo_path = ? WHERE id = ? AND user_id = ?"
    );
    
    try {
        $stmt->execute([$name, $description, $price, $duration, $photo_path, $id, $user_id]);
        header('Location: services.php?message=Serviço atualizado com sucesso!');
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header('Location: edit_service.php?id=' . $id . '&error=Erro ao salvar no banco de dados.');
        exit();
    }
} else {
    header('Location: services.php');
    exit();
}
?>
