<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/csrf.php';
require_once 'templates/header.php';

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-3">
    <h2 class="mb-3 mb-md-0"><i class="bi bi-card-checklist"></i> Meus Serviços</h2>
    <a href="add_service.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Adicionar Novo Serviço</a>
</div>

<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<?php if (empty($services)):
?>
    <div class="text-center p-5 mb-4 bg-light rounded-3">
        <i class="bi bi-card-checklist" style="font-size: 4rem;"></i>
        <h2 class="mt-3">Nenhum serviço cadastrado</h2>
        <p class="lead">Adicione os serviços que você oferece para começar a agendar seus clientes.</p>
        <a href="add_service.php" class="btn btn-primary btn-lg mt-3"><i class="bi bi-plus-circle"></i> Adicionar seu primeiro serviço</a>
    </div>
<?php else:
?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%;">Foto</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Preço</th>
                        <th>Duração</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service):
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($service['photo_path'])):
                                ?>
                                    <img src="<?php echo htmlspecialchars($service['photo_path']); ?>" alt="Foto de <?php echo htmlspecialchars($service['name']); ?>" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                <?php else:
                                ?>
                                    <div class="bg-secondary d-flex align-items-center justify-content-center text-white img-thumbnail" style="width: 80px; height: 60px;">
                                        <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                                    </div>
                                <?php endif;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($service['price'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($service['duration']); ?> min</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="actionsMenuButton_<?php echo $service['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsMenuButton_<?php echo $service['id']; ?>">
                                        <li>
                                            <a class="dropdown-item" href="edit_service.php?id=<?php echo $service['id']; ?>">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="delete_service.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                                <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
<?php endif;
?>

<?php
require_once 'templates/footer.php';
?>
