<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'templates/header.php';

$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

// --- Estatísticas ---
// Total de Clientes
$stmt_clients = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE user_id = ?");
$stmt_clients->execute([$user_id]);
$total_clients = $stmt_clients->fetchColumn();

// Total de Serviços
$stmt_services = $pdo->prepare("SELECT COUNT(*) FROM services WHERE user_id = ?");
$stmt_services->execute([$user_id]);
$total_services = $stmt_services->fetchColumn();

// Agendamentos para hoje
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');
$stmt_today = $pdo->prepare("SELECT a.start_datetime, c.name as client_name, s.name FROM appointments a JOIN clientes c ON a.client_id = c.id JOIN services s ON a.service_id = s.id WHERE a.user_id = ? AND a.start_datetime BETWEEN ? AND ? ORDER BY a.start_datetime");
$stmt_today->execute([$user_id, $today_start, $today_end]);
$today_appointments = $stmt_today->fetchAll(PDO::FETCH_ASSOC);
$total_today_appointments = count($today_appointments);

?>

<h1 class="display-5 fw-bold mb-4">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people"></i> Total de Clientes</h5>
                <p class="card-text fs-2"><?php echo $total_clients; ?></p>
                <a href="clients.php" class="btn btn-primary">Ver Clientes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-card-checklist"></i> Total de Serviços</h5>
                <p class="card-text fs-2"><?php echo $total_services; ?></p>
                <a href="services.php" class="btn btn-primary">Ver Serviços</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-day"></i> Agendamentos Hoje</h5>
                <p class="card-text fs-2"><?php echo $total_today_appointments; ?></p>
                <a href="agenda.php" class="btn btn-primary">Ver Agenda</a>
            </div>
        </div>
    </div>
</div>

<!-- Próximos Agendamentos -->
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-clock"></i> Agendamentos de Hoje</h3>
    </div>
    <div class="card-body">
        <?php if (empty($today_appointments)):
        ?>
            <p class="text-center">Nenhum agendamento para hoje. Aproveite o dia!</p>
        <?php else:
        ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($today_appointments as $apt):
                ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold"><?php echo date('H:i', strtotime($apt['start_datetime'])); ?></span> - 
                            <?php echo htmlspecialchars($apt['client_name']); ?>
                            <small class="text-muted">(<?php echo htmlspecialchars($apt['name']); ?>)</small>
                        </div>
                        <a href="agenda.php" class="btn btn-sm btn-outline-primary">Ir para Agenda</a>
                    </li>
                <?php endforeach;
                ?>
            </ul>
        <?php endif;
        ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>