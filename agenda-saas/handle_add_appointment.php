<?php
header('Content-Type: application/json');

require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // O corpo da requisição AJAX é JSON
    $input = json_decode(file_get_contents('php://input'), true);

    validate_csrf_token($input['csrf_token'] ?? '');

    $user_id = $_SESSION['user_id'];
    $client_id = $input['client_id'] ?? null;
    $service_id = $input['service_id'] ?? null;
    $start_date = $input['start_date'] ?? null;
    $start_time = $input['start_time'] ?? null;
    $notes = $input['notes'] ?? '';

    if (!$client_id || !$service_id || !$start_date || !$start_time) {
        throw new Exception('Todos os campos são obrigatórios.');
    }

    $pdo = getDbConnection();

    // Pega a duração do serviço para calcular o tempo final
    $stmt_duration = $pdo->prepare("SELECT duration FROM services WHERE id = ? AND user_id = ?");
    $stmt_duration->execute([$service_id, $user_id]);
    $service = $stmt_duration->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        throw new Exception('Serviço inválido.');
    }

    $duration_minutes = $service['duration'];
    $start_datetime_str = $start_date . ' ' . $start_time;
    $start_datetime = new DateTime($start_datetime_str);
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $duration_minutes . 'M'));

    $stmt = $pdo->prepare(
        'INSERT INTO appointments (user_id, client_id, service_id, start_datetime, end_datetime, notes) VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $user_id,
        $client_id,
        $service_id,
        $start_datetime->format('Y-m-d H:i:s'),
        $end_datetime->format('Y-m-d H:i:s'),
        $notes
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Agendamento criado com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
