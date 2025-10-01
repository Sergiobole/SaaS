<?php
require_once 'src/auth_check.php';
require_once 'config.php';

// Inclui o autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Apenas processa se for um POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clients.php');
    exit();
}

$mail = new PHPMailer(true);
$message = '';
$error = false;

try {
    // Configurações do Servidor
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Desligue a depuração para produção. Use SMTP::DEBUG_SERVER para diagnosticar.
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST; // Ex: 'smtp.gmail.com'
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER; // Seu email completo
    $mail->Password   = SMTP_PASS; // Use uma "Senha de App", não a sua senha normal
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Criptografia TLS
    $mail->Port       = 587; // Porta para TLS
    $mail->CharSet    = 'UTF-8';

    // Remetente e Destinatário
    $mail->setFrom(SMTP_USER, $_SESSION['business_name']);
    $mail->addAddress($_POST['client_email'], $_POST['client_name']);
    $mail->addReplyTo(SMTP_USER, $_SESSION['business_name']);

    // Anexos
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
    }

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = $_POST['subject'];
    $mail->Body    = nl2br(htmlspecialchars($_POST['body']));
    $mail->AltBody = htmlspecialchars($_POST['body']);

    $mail->send();
    $message = 'Email enviado com sucesso!';

} catch (Exception $e) {
    $error = true;
    // Para o dev, logar o erro completo. Para o usuário, uma mensagem amigável.
    error_log("Mailer Error: " . $mail->ErrorInfo);
    $message = "Não foi possível enviar o e-mail. Erro: " . $mail->ErrorInfo;
}

// Redireciona de volta para a página de clientes com a mensagem apropriada
$param = $error ? 'error' : 'message';
header("Location: clients.php?{$param}=" . urlencode($message));
exit();
?>