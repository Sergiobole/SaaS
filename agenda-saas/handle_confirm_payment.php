<?php
header('Content-Type: application/json');

require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/csrf.php';

$data = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token
try {
    validate_csrf_token($data['csrf_token'] ?? '');
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do agendamento não fornecido.']);
    exit();
}

$appointment_id = $data['id'];
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'UPDATE appointments SET status = \'paid\' WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$appointment_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Pagamento confirmado com sucesso!']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Agendamento não encontrado ou já está pago.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>