<?php
require_once 'src/auth_check.php';
require_once 'src/csrf.php';
require_once 'templates/header.php';
?>

<h2><i class="bi bi-plus-circle"></i> Adicionar Novo Serviço</h2>
<hr>

<form action="handle_add_service.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="name" class="form-label">Nome do Serviço</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="photo" class="form-label">Foto do Serviço</label>
            <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="price" class="form-label">Preço (R$)</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="duration" class="form-label">Duração (minutos)</label>
            <input type="number" class="form-control" id="duration" name="duration" min="1" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Descrição</label>
        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Salvar Serviço</button>
    <a href="services.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
require_once 'templates/footer.php';
?>
