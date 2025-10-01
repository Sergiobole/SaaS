<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

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

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO services (user_id, name, description, price, duration) VALUES (?, ?, ?, ?, ?)"
    );
    
    try {
        $stmt->execute([$user_id, $name, $description, $price, $duration]);
        header('Location: services.php?message=Serviço adicionado com sucesso!');
        exit();
    } catch (PDOException $e) {
        // Em um ambiente de produção, logar o erro em vez de exibi-lo.
        error_log($e->getMessage());
        header('Location: services.php?error=Erro ao adicionar serviço.');
        exit();
    }
} else {
    header('Location: add_service.php');
    exit();
}
?>
