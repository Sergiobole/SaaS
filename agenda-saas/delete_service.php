<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: services.php?error=Ação inválida.');
    exit();
}

validate_csrf_token($_POST['csrf_token']);

$id = $_POST['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header('Location: services.php');
    exit();
}

$pdo = getDbConnection();
// Garante que o usuário só pode apagar os próprios serviços
$stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND user_id = ?");

try {
    $stmt->execute([$id, $user_id]);
    if ($stmt->rowCount() > 0) {
        header('Location: services.php?message=Serviço excluído com sucesso!');
    } else {
        // O serviço não foi encontrado ou não pertence ao usuário
        header('Location: services.php?error=Não foi possível excluir o serviço.');
    }
    exit();
} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: services.php?error=Erro ao excluir serviço.');
    exit();
}
?>
