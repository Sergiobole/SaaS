<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'templates/header.php';

$token = generate_csrf_token();
?>

<h2><i class="bi bi-plus-circle"></i> Adicionar Novo Cliente</h2>

<form action="handle_add_client.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto do Cliente</label>
                        <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Observações</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                <div class="form-text">Ex: Alergias, preferências, detalhes do pet, etc.</div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="clients.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Cliente</button>
        </div>
    </div>
</form>

<?php
require_once 'templates/footer.php';
?>