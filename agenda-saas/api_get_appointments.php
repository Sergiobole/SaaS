<?php
header('Content-Type: application/json');

require_once 'src/auth_check.php';
require_once 'src/database.php';

$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

// O FullCalendar envia as datas start e end do período que ele está visualizando
$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare(
        'SELECT 
            a.id, 
            a.start_datetime, 
            a.end_datetime, 
            a.notes, 
            a.status,
            a.client_id,
            a.service_id,
            c.name as client_name, 
            s.name as service_name
        FROM appointments a
        JOIN clientes c ON a.client_id = c.id
        JOIN services s ON a.service_id = s.id
        WHERE a.user_id = ? AND a.start_datetime BETWEEN ? AND ?'
    );
    $stmt->execute([$user_id, $start, $end]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($appointments as $apt) {
        $events[] = [
            'id' => $apt['id'],
            'title' => $apt['client_name'] . ' - ' . $apt['service_name'],
            'start' => $apt['start_datetime'],
            'end' => $apt['end_datetime'],
            'extendedProps' => [
                'notes' => $apt['notes'],
                'status' => $apt['status'],
                'client_id' => $apt['client_id'],
                'service_id' => $apt['service_id']
            ]
        ];
    }

    echo json_encode($events);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar agendamentos: ' . $e->getMessage()]);
}
?>
