<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'templates/header.php';

$client_id = $_GET['id'] ?? null;
if (!$client_id) {
    header('Location: clients.php');
    exit();
}

$pdo = getDbConnection();
// Garante que o cliente pertence ao usuário logado
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND user_id = ?");
$stmt->execute([$client_id, $_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die('Cliente não encontrado ou acesso não permitido.');
}
?>

<h2><i class="bi bi-pencil"></i> Editar Cliente</h2>

<form action="handle_edit_client.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
    <div class="card">
        <div class="card-body">
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
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($client['notes']); ?></textarea>
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