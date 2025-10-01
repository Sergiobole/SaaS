<?php

session_start();
require_once 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="text-center">Login</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success">Cadastro realizado com sucesso! Faça o login.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="handle_login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <p class="text-center mt-3">
            Não tem uma conta? <a href="register.php">Cadastre-se</a>
        </p>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>