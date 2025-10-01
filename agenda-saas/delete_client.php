<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

// A exclusão deve ser feita via POST para segurança (evitar CSRF)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clients.php?error=Ação inválida.');
    exit();
}

validate_csrf_token($_POST['csrf_token']);

$id = $_POST['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header('Location: clients.php');
    exit();
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ? AND user_id = ?");

try {
    $stmt->execute([$id, $user_id]);
    header('Location: clients.php?message=Cliente excluído com sucesso!');
    exit();
} catch (PDOException $e) {
    die("Erro ao excluir cliente: " . $e->getMessage());
}
?>