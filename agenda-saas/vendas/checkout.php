<?php include_once '../templates/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Finalizar Compra</h2>
                </div>
                <div class="card-body p-4">
                    <p>Você está adquirindo o plano de <strong>Acesso Completo por 30 Dias</strong>.</p>
                    <p class="h4">Valor: <strong>R$ 29,90</strong></p>
                    <hr>
                    <p class="lead">Pague com PIX para liberação imediata.</p>
                    
                    <form action="gerar_pix.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label h5">Seu Melhor E-mail</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="email@exemplo.com" required>
                            <div class="form-text">Seu token de acesso será enviado para este e-mail após a confirmação do pagamento.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Gerar PIX e QR Code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>