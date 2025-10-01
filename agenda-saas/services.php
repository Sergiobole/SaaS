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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-card-checklist"></i> Meus Serviços</h2>
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
            <table class="table table-hover">
                <thead>
                    <tr>
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
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                            <td>R$ <?php echo htmlspecialchars(number_format($service['price'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($service['duration']); ?> min</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="delete_service.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                        <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif;
?>

<?php
require_once 'templates/footer.php';
?>
