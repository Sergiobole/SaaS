<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda e Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex flex-column vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><?php echo htmlspecialchars($_SESSION['business_name'] ?? 'Painel'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'agenda.php') ? 'active' : ''; ?>" href="agenda.php"><i class="bi bi-calendar-week"></i> Agenda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['clients.php', 'add_client.php', 'edit_client.php']) ? 'active' : ''; ?>" href="clients.php"><i class="bi bi-people"></i> Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['services.php', 'add_service.php', 'edit_service.php']) ? 'active' : ''; ?>" href="services.php"><i class="bi bi-card-checklist"></i> Serviços</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">