<?php
session_start();
require_once 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center">Cadastro de Novo Profissional</h2>
        <form action="handle_register.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="business_name" class="form-label">Nome do Negócio</label>
                <input type="text" class="form-control" id="business_name" name="business_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
        </form>
        <p class="text-center mt-3">
            Já tem uma conta? <a href="login.php">Faça login</a>
        </p>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>