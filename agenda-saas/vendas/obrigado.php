<?php
session_start();

// Apenas permite o acesso se o pagamento foi processado
if (!isset($_SESSION['payment_success']) || !$_SESSION['payment_success']) {
    header('Location: index.php');
    exit;
}

$token = $_SESSION['sent_token'];
$email = $_SESSION['sent_email'];
$error_message = isset($_SESSION['payment_error']) ? $_SESSION['payment_error'] : null;

// Limpa a sessão para não poder acessar esta página novamente
unset($_SESSION['payment_success']);
unset($_SESSION['sent_token']);
unset($_SESSION['sent_email']);
unset($_SESSION['payment_error']);

include_once '../templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-header bg-success text-white">
                    <h2>Pagamento Aprovado!</h2>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Obrigado por sua compra!</h5>
                    <p>Seu token de acesso foi gerado com sucesso.</p>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><strong>Atenção:</strong> <?php echo htmlspecialchars($error_message); ?></div>
                    <?php else: ?>
                        <div class="alert alert-success">Enviamos o token para o seu e-mail: <strong><?php echo htmlspecialchars($email); ?></strong></div>
                    <?php endif; ?>

                    <p>Guarde este token em um lugar seguro. Você precisará dele para se registrar.</p>
                    
                    <div class="alert alert-info" style="font-size: 1.5rem; font-weight: bold;">
                        <?php echo htmlspecialchars($token); ?>
                    </div>

                    <a href="../register.php" class="btn btn-primary btn-lg mt-3">Ir para a Página de Registro</a>
                </div>
                <div class="card-footer text-muted">
                    Token válido por 30 dias para um único registro.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php';
?>