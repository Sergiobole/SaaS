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

<form action="handle_edit_service.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
    <input type="hidden" name="existing_photo_path" value="<?php echo htmlspecialchars($service['photo_path'] ?? ''); ?>">

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <h5 class="mb-3">Foto do Serviço</h5>
                    <?php if (!empty($service['photo_path'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($service['photo_path']); ?>" alt="Foto de <?php echo htmlspecialchars($service['name']); ?>" class="img-thumbnail mb-3" style="width: 100%; object-fit: cover;">
                    <?php else:
                    ?>
                        <div class="bg-secondary d-flex align-items-center justify-content-center text-white img-thumbnail mx-auto mb-3" style="width: 100%; height: 200px;">
                            <i class="bi bi-image" style="font-size: 5rem;"></i>
                        </div>
                    <?php endif;
                    ?>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Alterar Foto</label>
                        <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Serviço</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Preço (R$)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($service['price']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duração (minutos)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="1" value="<?php echo htmlspecialchars($service['duration']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($service['description']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="services.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </div>
</form>

<?php
require_once 'templates/footer.php';
?>
