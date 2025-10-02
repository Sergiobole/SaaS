<?php
session_start();
require_once 'config.php';
require_once '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || empty($_POST['email'])) {
    header("Location: checkout.php");
    exit;
}

$email_cliente = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email_cliente) {
    die("Email inválido.");
}

// --- Conexão com o banco de dados de VENDAS ---
function pdo_connect_vendas() {
    try {
        return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    } catch (PDOException $exception) {
        exit('Falha ao conectar ao banco de dados de validação!');
    }
}

// 1. Configurar o Mercado Pago
MercadoPago\SDK::setAccessToken(MP_ACCESS_TOKEN);

// 2. Criar o item de pagamento
$payment = new MercadoPago\Payment();
$payment->transaction_amount = 29.90;
$payment->description = "Token de Acesso 30 Dias - Agenda SaaS";
$payment->payment_method_id = "pix";

$payment->payer = new MercadoPago\Payer();
$payment->payer->email = $email_cliente;

// 3. Configurar a URL de notificação (Webhook)
// IMPORTANTE: Esta URL DEVE ser acessível publicamente. Use um serviço como Ngrok para testes locais.
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$payment->notification_url = $base_url . '/webhook_mercado_pago.php';

// 4. Salvar o pagamento
$payment->save();

// 5. Verificar se o pagamento foi criado
if ($payment->id) {
    // Salvar o ID do pagamento e o email no nosso banco de dados
    try {
        $pdo = pdo_connect_vendas();
        $stmt = $pdo->prepare('INSERT INTO pix_payments (mp_payment_id, email_cliente, status) VALUES (?, ?, ?)');
        $stmt->execute([$payment->id, $email_cliente, 'pending']);
    } catch (PDOException $e) {
        // Lidar com o erro de banco de dados
        die("Erro ao salvar os dados do pagamento: " . $e->getMessage());
    }

    // Guardar dados na sessão para a próxima página
    $_SESSION['payment_id'] = $payment->id;
    $_SESSION['qr_code_base64'] = $payment->point_of_interaction->transaction_data->qr_code_base64;
    $_SESSION['qr_code'] = $payment->point_of_interaction->transaction_data->qr_code;

    // Redirecionar para a página de aguardo
    header("Location: aguardando_pagamento.php");
    exit;
} else {
    // Lidar com erro na criação do pagamento
    die("Ocorreu um erro ao gerar o PIX. Verifique suas credenciais do Mercado Pago.");
}
