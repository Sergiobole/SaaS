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
    $client_id = $input['client_id'] ?? null;
    $service_ids = $input['service_ids'] ?? [];
    $start_date = $input['start_date'] ?? null;
    $start_time = $input['start_time'] ?? null;
    $notes = $input['notes'] ?? '';

    if (!$client_id || empty($service_ids) || !$start_date || !$start_time) {
        throw new Exception('Todos os campos são obrigatórios.');
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // Get services details (duration and price)
    $services_data = [];
    $total_duration = 0;
    if (!empty($service_ids)) {
        $placeholders = implode(',', array_fill(0, count($service_ids), '?'));
        $stmt_services = $pdo->prepare("SELECT id, duration, price FROM services WHERE id IN ($placeholders) AND user_id = ?");
        
        $params = $service_ids;
        $params[] = $user_id;
        $stmt_services->execute($params);
        
        $services_data = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

        if (count($services_data) !== count($service_ids)) {
            $pdo->rollBack();
            throw new Exception('Um ou mais serviços são inválidos ou não pertencem a você.');
        }

        foreach ($services_data as $service) {
            $total_duration += $service['duration'];
        }
    }

    if ($total_duration <= 0) {
        throw new Exception('A duração total dos serviços deve ser maior que zero.');
    }

    $start_datetime_str = $start_date . ' ' . $start_time;
    $start_datetime = new DateTime($start_datetime_str);
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $total_duration . 'M'));

    $stmt = $pdo->prepare(
        'INSERT INTO appointments (user_id, client_id, start_datetime, end_datetime, notes) VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $user_id,
        $client_id,
        $start_datetime->format('Y-m-d H:i:s'),
        $end_datetime->format('Y-m-d H:i:s'),
        $notes
    ]);

    $appointment_id = $pdo->lastInsertId();

    // Insert into the appointment_services pivot table with price
    $stmt_pivot = $pdo->prepare(
        'INSERT INTO appointment_services (appointment_id, service_id, price) VALUES (?, ?, ?)'
    );
    foreach ($services_data as $service) {
        $stmt_pivot->execute([$appointment_id, $service['id'], $service['price']]);
    }

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Agendamento criado com sucesso!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
