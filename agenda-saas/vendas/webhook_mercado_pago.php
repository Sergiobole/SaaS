<?php
// Carrega configurações e dependências
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Funções de Apoio ---

/**
 * Escreve uma mensagem de log em um arquivo.
 * @param string $message A mensagem para registrar.
 */
function write_log($message) {
    $logFile = __DIR__ . '/webhook_debug.log';
    error_log(date('[Y-m-d H:i:s]') . " " . $message . "\n", 3, $logFile);
}

/**
 * Envia uma resposta HTTP e termina o script.
 * @param int $statusCode O código de status HTTP.
 * @param string $message A mensagem a ser registrada.
 */
function send_response($statusCode, $message) {
    http_response_code($statusCode);
    if (!empty($message)) {
        write_log($message);
    }
    exit;
}

/**
 * Conecta ao banco de dados de vendas.
 * @return PDO A conexão PDO.
 */
function pdo_connect_vendas() {
    try {
        return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        send_response(500, "Falha ao conectar ao banco de dados: " . $e->getMessage());
    }
}

/**
 * Verifica a assinatura do webhook do Mercado Pago.
 * @param string $secret A chave secreta do webhook.
 */
function verify_signature($secret) {
    $signatureHeader = isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : '';
    if (empty($signatureHeader)) {
        send_response(400, "Assinatura do webhook ausente.");
    }

    parse_str($signatureHeader, $signatureParts);
    $ts = $signatureParts['ts'];
    $hash = $signatureParts['v1'];
    $body = file_get_contents('php://input');
    
    $manifest = "id:{$_GET['data_id']};request-id:{\$_SERVER['HTTP_X_REQUEST_ID']};ts:{$ts};";
    $expectedHash = hash_hmac('sha256', $manifest, $secret);

    if (!hash_equals($expectedHash, $hash)) {
        send_response(403, "Assinatura do webhook inválida.");
    }
}


// --- Início do Processamento do Webhook ---

write_log("Webhook recebido. Início do processamento.");

// 1. Validação de Segurança
if (!defined('MP_WEBHOOK_SECRET') || MP_WEBHOOK_SECRET === 'SUA_SECRET_KEY_DO_WEBHOOK_AQUI') {
    send_response(500, "A chave secreta do webhook (MP_WEBHOOK_SECRET) não está configurada.");
}
// A verificação da assinatura foi desativada temporariamente para facilitar o teste inicial.
// Descomente a linha abaixo para ativar a segurança em produção.
// verify_signature(MP_WEBHOOK_SECRET);


// 2. Obtenção dos Dados
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    send_response(400, "Corpo da requisição JSON inválido.");
}

if (!isset($data['action'], $data['data']['id']) || $data['type'] !== 'payment') {
    send_response(200, "Notificação não relevante, ignorando.");
}

$mp_payment_id = $data['data']['id'];
write_log("Processando pagamento ID: {$mp_payment_id}");

try {
    // 3. Consulta do Pagamento na API do Mercado Pago
    MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);
    $client = new PaymentClient();
    $payment = $client->get($mp_payment_id);

    if (!$payment) {
        send_response(404, "Pagamento {$mp_payment_id} não encontrado na API do Mercado Pago.");
    }

    // 4. Processamento do Pagamento Aprovado
    if ($payment->status === 'approved') {
        write_log("Pagamento {$mp_payment_id} está com status 'approved'.");
        $pdo = pdo_connect_vendas();

        // Verifica se já não foi processado
        $stmt = $pdo->prepare("SELECT status, email_cliente FROM pix_payments WHERE mp_payment_id = ?");
        $stmt->execute([$mp_payment_id]);
        $our_payment = $stmt->fetch();

        if ($our_payment && $our_payment['status'] === 'pending') {
            write_log("Pagamento encontrado no banco de dados com status 'pending'. Atualizando.");
            
            $pdo->beginTransaction();

            // Atualiza o status
            $stmt = $pdo->prepare("UPDATE pix_payments SET status = 'approved' WHERE mp_payment_id = ?");
            $stmt->execute([$mp_payment_id]);

            // Gera e salva o token de acesso
            $email_cliente = $our_payment['email_cliente'];
            $token = bin2hex(random_bytes(16));
            $hashed_token = password_hash($token, PASSWORD_DEFAULT);
            $validade = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt = $pdo->prepare('INSERT INTO tokens_criados (token, email_cliente, data_validade, usado) VALUES (?, ?, ?, 0)');
            $stmt->execute([$hashed_token, $email_cliente, $validade]);
            
            $pdo->commit();
            write_log("Token gerado para o e-mail {$email_cliente}.");

            // Envia o e-mail com o token
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port = SMTP_PORT;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom(SMTP_USER, 'Sistema de Agenda');
                $mail->addAddress($email_cliente);
                $mail->isHTML(true);
                $mail->Subject = 'Seu Token de Acesso - Sistema de Agenda';
                $mail->Body = "Olá!<br><br>Seu pagamento foi aprovado! Obrigado por adquirir seu acesso.<br><br>Seu token de acesso é: <strong>" . $token . "</strong><br><br>Use este token para se registrar na plataforma.<br><br>Atenciosamente,<br>Equipe do Sistema de Agenda";
                $mail->send();
                write_log("E-mail com token enviado para {$email_cliente}.");
            } catch (Exception $e) {
                write_log("Falha ao enviar email para {$email_cliente}: " . $mail->ErrorInfo);
                // Não retorna erro para o MP, pois o pagamento foi processado com sucesso.
            }
        } else {
            write_log("Pagamento {$mp_payment_id} já processado ou não encontrado com status 'pending'. Ignorando.");
        }
    } else {
        write_log("Pagamento {$mp_payment_id} com status '{\$payment->status}'. Ignorando.");
    }

} catch (Exception $e) {
    // Se uma transação foi iniciada, desfaz
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    send_response(500, "Erro inesperado: " . $e->getMessage());
}

// 5. Responde ao Mercado Pago para confirmar o recebimento
send_response(200, "Processamento concluído com sucesso.");
?>
