<?php
session_start();

if (empty($_SESSION['payment_id'])) {
    header("Location: checkout.php");
    exit;
}

$payment_id = $_SESSION['payment_id'];
$qr_code_base64 = $_SESSION['qr_code_base64'];
$qr_code = $_SESSION['qr_code'];

// Limpa a sessão para não usar os mesmos dados de novo
// unset($_SESSION['payment_id']);
// unset($_SESSION['qr_code_base64']);
// unset($_SESSION['qr_code']);

include_once '../templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-header">
                    <h2>Pague com PIX para Finalizar</h2>
                </div>
                <div class="card-body p-4">
                    <p>Escaneie o QR Code abaixo com o app do seu banco:</p>
                    <img src="data:image/png;base64, <?php echo $qr_code_base64; ?>" alt="PIX QR Code" class="img-fluid mb-3">
                    
                    <p>Ou use o código "Copia e Cola":</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="<?php echo $qr_code; ?>" id="pixCode">
                        <button class="btn btn-outline-secondary" type="button" id="copyButton">Copiar</button>
                    </div>

                    <hr>

                    <div id="status-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2"><strong>Aguardando confirmação do pagamento...</strong></p>
                        <p class="text-muted">Pode levar alguns segundos. Não feche esta página.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.getElementById('copyButton');
    const pixCode = document.getElementById('pixCode');

    copyButton.addEventListener('click', function() {
        pixCode.select();
        document.execCommand('copy');
        copyButton.textContent = 'Copiado!';
        setTimeout(() => { copyButton.textContent = 'Copiar'; }, 2000);
    });

    const paymentId = <?php echo json_encode($payment_id); ?>;
    const statusCheckInterval = setInterval(() => {
        fetch(`verificar_status_pix.php?payment_id=${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'approved') {
                    clearInterval(statusCheckInterval);
                    document.getElementById('status-container').innerHTML = '<div class="alert alert-success"><h5>Pagamento Aprovado!</h5><p>Seu token foi enviado para o seu e-mail. Redirecionando...</p></div>';
                    setTimeout(() => {
                        window.location.href = 'obrigado.php';
                    }, 3000);
                }
            })
            .catch(error => console.error('Erro ao verificar status:', error));
    }, 5000); // Verifica a cada 5 segundos
});
</script>

<?php include_once '../templates/footer.php'; ?>