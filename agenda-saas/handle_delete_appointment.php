<?php
header('Content-Type: application/json');

require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    validate_csrf_token($input['csrf_token'] ?? '');

    $user_id = $_SESSION['user_id'];
    $id = $input['id'] ?? null;

    if (!$id) {
        throw new Exception('ID do agendamento é obrigatório.');
    }

    $pdo = getDbConnection();

    $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Agendamento não encontrado ou não pertence a você.');
    }

    echo json_encode(['status' => 'success', 'message' => 'Agendamento excluído com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
