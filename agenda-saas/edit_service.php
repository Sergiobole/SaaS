<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'src/database.php';
require_once 'templates/header.php';

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header('Location: services.php');
    exit();
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    // Se não encontrou o serviço ou não pertence ao usuário, redireciona
    header('Location: services.php?error=Serviço não encontrado.');
    exit();
}
?>

<h2><i class="bi bi-pencil"></i> Editar Serviço</h2>
<hr>

<form action="handle_edit_service.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nome do Serviço</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
            <label for="price" class="form-label">Preço (R$)</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($service['price']); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
            <label for="duration" class="form-label">Duração (minutos)</label>
            <input type="number" class="form-control" id="duration" name="duration" min="1" value="<?php echo htmlspecialchars($service['duration']); ?>" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Descrição</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($service['description']); ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    <a href="services.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
require_once 'templates/footer.php';
?>
