<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';
require_once 'src/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (empty($name) || is_null($price) || is_null($duration)) {
        header('Location: services.php?error=Nome, preço e duração são obrigatórios.');
        exit();
    }

    // Lida com o upload da foto
    $photo_path = handle_photo_upload($_FILES['photo'], 'service_photos');

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO services (user_id, name, description, price, duration, photo_path) VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    try {
        $stmt->execute([$user_id, $name, $description, $price, $duration, $photo_path]);
        header('Location: services.php?message=Serviço adicionado com sucesso!');
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header('Location: services.php?error=Erro ao adicionar serviço.');
        exit();
    }
} else {
    header('Location: add_service.php');
    exit();
}
?>
