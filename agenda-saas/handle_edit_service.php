<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token']);

    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (empty($name) || is_null($price) || is_null($duration) || !$id) {
        header('Location: services.php?error=Dados inválidos.');
        exit();
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "UPDATE services SET name = ?, description = ?, price = ?, duration = ? WHERE id = ? AND user_id = ?"
    );
    
    try {
        $stmt->execute([$name, $description, $price, $duration, $id, $user_id]);
        header('Location: services.php?message=Serviço atualizado com sucesso!');
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header('Location: services.php?error=Erro ao atualizar serviço.');
        exit();
    }
} else {
    header('Location: services.php');
    exit();
}
?>
