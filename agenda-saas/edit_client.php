<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/csrf.php'; // Adicionado para gerar token
require_once 'templates/header.php';

$client_id = $_GET['id'] ?? null;
if (!$client_id) {
    header('Location: clients.php');
    exit();
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND user_id = ?");
$stmt->execute([$client_id, $_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die('Cliente não encontrado ou acesso não permitido.');
}
?>

<h2><i class="bi bi-pencil"></i> Editar Cliente</h2>

<form action="handle_edit_client.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
    <input type="hidden" name="existing_photo_path" value="<?php echo htmlspecialchars($client['photo_path'] ?? ''); ?>">

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <h5 class="mb-3">Foto de Perfil</h5>
                    <?php if (!empty($client['photo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($client['photo_path']); ?>" alt="Foto de <?php echo htmlspecialchars($client['name']); ?>" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width: 150px; height: 150px;">
                            <i class="bi bi-person-fill" style="font-size: 5rem;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Alterar Foto</label>
                        <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5"><?php echo htmlspecialchars($client['notes']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="clients.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </div>
</form>

<?php
require_once 'templates/footer.php';
?>