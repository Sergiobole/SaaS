<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'templates/header.php';

$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

// --- 1. DADOS PARA AGENDAMENTOS PASSADOS (MÊS ATUAL) ---
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

// Total de agendamentos no mês
$stmt_month_total = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND start_datetime BETWEEN ? AND ?");
$stmt_month_total->execute([$user_id, $current_month_start, $current_month_end]);
$month_total_appointments = $stmt_month_total->fetchColumn();

// Faturamento total do mês (status 'pago')
$stmt_month_revenue = $pdo->prepare(
    "SELECT SUM(as_.price) 
     FROM appointments a
     JOIN appointment_services as_ ON a.id = as_.appointment_id
     WHERE a.user_id = ? AND a.status = 'pago' AND a.start_datetime BETWEEN ? AND ?"
);
$stmt_month_revenue->execute([$user_id, $current_month_start, $current_month_end]);
$month_total_revenue = $stmt_month_revenue->fetchColumn() ?? 0;


// --- 2. DADOS PARA AGENDAMENTOS DE HOJE ---
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// Total de clientes hoje
$stmt_today_total = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND start_datetime BETWEEN ? AND ?");
$stmt_today_total->execute([$user_id, $today_start, $today_end]);
$today_total_appointments = $stmt_today_total->fetchColumn();

// Previsão de encerramento hoje
$stmt_today_end_time = $pdo->prepare("SELECT MAX(end_datetime) FROM appointments WHERE user_id = ? AND start_datetime BETWEEN ? AND ?");
$stmt_today_end_time->execute([$user_id, $today_start, $today_end]);
$today_last_end_time = $stmt_today_end_time->fetchColumn();

// Faturamento do dia (status 'pago')
$stmt_today_revenue = $pdo->prepare(
    "SELECT SUM(as_.price) 
     FROM appointments a
     JOIN appointment_services as_ ON a.id = as_.appointment_id
     WHERE a.user_id = ? AND a.status = 'pago' AND a.start_datetime BETWEEN ? AND ?"
);
$stmt_today_revenue->execute([$user_id, $today_start, $today_end]);
$today_total_revenue = $stmt_today_revenue->fetchColumn() ?? 0;


// --- 3. DADOS PARA AGENDAMENTOS DE AMANHÃ ---
$tomorrow_start = date('Y-m-d 00:00:00', strtotime('+1 day'));
$tomorrow_end = date('Y-m-d 23:59:59', strtotime('+1 day'));

// Total de clientes amanhã
$stmt_tomorrow_total = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND start_datetime BETWEEN ? AND ?");
$stmt_tomorrow_total->execute([$user_id, $tomorrow_start, $tomorrow_end]);
$tomorrow_total_appointments = $stmt_tomorrow_total->fetchColumn();

// Previsão de encerramento amanhã
$stmt_tomorrow_end_time = $pdo->prepare("SELECT MAX(end_datetime) FROM appointments WHERE user_id = ? AND start_datetime BETWEEN ? AND ?");
$stmt_tomorrow_end_time->execute([$user_id, $tomorrow_start, $tomorrow_end]);
$tomorrow_last_end_time = $stmt_tomorrow_end_time->fetchColumn();

// Faturamento de amanhã (status 'pago')
$stmt_tomorrow_revenue = $pdo->prepare(
    "SELECT SUM(as_.price) 
     FROM appointments a
     JOIN appointment_services as_ ON a.id = as_.appointment_id
     WHERE a.user_id = ? AND a.status = 'pago' AND a.start_datetime BETWEEN ? AND ?"
);
$stmt_tomorrow_revenue->execute([$user_id, $tomorrow_start, $tomorrow_end]);
$tomorrow_total_revenue = $stmt_tomorrow_revenue->fetchColumn() ?? 0;

?>

<h1 class="display-5 fw-bold mb-4">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

<div class="row">
<div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-check"></i> Agendamentos Passados (Mês)</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Agendamentos Totais
                        <span class="badge bg-primary rounded-pill fs-6"><?php echo $month_total_appointments; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Faturamento (Pago)
                        <span class="badge bg-success rounded-pill fs-6">R$ <?php echo number_format($month_total_revenue, 2, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="report_monthly.php" target="_blank" class="btn btn-primary"><i class="bi bi-download"></i> Exportar Relatório</a>
            </div>
        </div>
    </div>

<div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-day"></i> Agendamentos Hoje</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total de Clientes
                        <span class="badge bg-primary rounded-pill fs-6"><?php echo $today_total_appointments; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Encerramento do Último
                        <span class="badge bg-info rounded-pill fs-6"><?php echo $today_last_end_time ? date('H:i', strtotime($today_last_end_time)) : 'N/A'; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Faturamento (Pago)
                        <span class="badge bg-success rounded-pill fs-6">R$ <?php echo number_format($today_total_revenue, 2, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="report_daily.php?day=today" target="_blank" class="btn btn-primary"><i class="bi bi-download"></i> Exportar Relatório do Dia</a>
            </div>
        </div>
    </div>

    <!-- Coluna Agendamentos Amanhã -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-plus"></i> Agendamentos Amanhã</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total de Clientes
                        <span class="badge bg-primary rounded-pill fs-6"><?php echo $tomorrow_total_appointments; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Encerramento do Último
                        <span class="badge bg-info rounded-pill fs-6"><?php echo $tomorrow_last_end_time ? date('H:i', strtotime($tomorrow_last_end_time)) : 'N/A'; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Faturamento (Pago)
                        <span class="badge bg-success rounded-pill fs-6">R$ <?php echo number_format($tomorrow_total_revenue, 2, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="report_daily.php?day=tomorrow" target="_blank" class="btn btn-primary"><i class="bi bi-download"></i> Exportar Relatório do Dia</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>