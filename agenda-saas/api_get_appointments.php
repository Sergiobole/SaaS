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
    // Updated query to support multiple services
    $stmt = $pdo->prepare(
        'SELECT 
            a.id, 
            a.start_datetime, 
            a.end_datetime, 
            a.notes, 
            a.status,
            a.client_id,
            c.name as client_name, 
            GROUP_CONCAT(s.id SEPARATOR \',\') as service_ids,
            GROUP_CONCAT(s.name SEPARATOR \', \') as service_names
        FROM appointments a
        JOIN clientes c ON a.client_id = c.id
        LEFT JOIN appointment_services as_ ON a.id = as_.appointment_id
        LEFT JOIN services s ON as_.service_id = s.id
        WHERE a.user_id = ? AND a.start_datetime BETWEEN ? AND ?
        GROUP BY a.id'
    );
    $stmt->execute([$user_id, $start, $end]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($appointments as $apt) {
        // Convert comma-separated string of IDs to an array of strings
        $service_ids = $apt['service_ids'] ? explode(',', $apt['service_ids']) : [];

        $events[] = [
            'id' => $apt['id'],
            'title' => $apt['client_name'] . ' - ' . ($apt['service_names'] ?? 'N/A'),
            'start' => $apt['start_datetime'],
            'end' => $apt['end_datetime'],
            'extendedProps' => [
                'notes' => $apt['notes'],
                'status' => $apt['status'],
                'client_id' => $apt['client_id'],
                'service_ids' => $service_ids // Pass service_ids as an array
            ]
        ];
    }

    echo json_encode($events);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar agendamentos: ' . $e->getMessage()]);
}
?>
