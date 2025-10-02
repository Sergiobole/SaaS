<?php
require_once 'src/csrf.php';
// Não incluir o header padrão pois esta página é para não-logados
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Agenda SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Crie sua Conta</h2>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <form action="handle_register.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="business_name" class="form-label">Nome da Empresa</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cpf_cnpj" class="form-label">CPF ou CNPJ</label>
                                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="+55 (XX) XXXXX-XXXX" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="access_token" class="form-label">Token de Acesso</label>
                            <input type="text" class="form-control form-control-lg" id="access_token" name="access_token" placeholder="Cole aqui o token que você recebeu" required>
                            <div class="form-text">Você recebe um token de acesso após adquirir um plano.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Criar Conta e Ativar Acesso</button>
                    </form>
                    <p class="text-center mt-4">
                        Já tem uma conta? <a href="login.php">Faça login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>