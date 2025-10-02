<?php
session_start();
require_once 'config.php';
require_once '../vendor/autoload.php';
require_once '../src/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $pdo = pdo_connect_mysql();

            // Gerar um token único
            $token = bin2hex(random_bytes(16));
            $hashed_token = password_hash($token, PASSWORD_DEFAULT);

            // Definir data de expiração para 30 dias
            $validade = date('Y-m-d H:i:s', strtotime('+30 days'));

            // Inserir o token no banco de dados
            $stmt = $pdo->prepare('INSERT INTO tokens_criados (token, email_cliente, data_validade, usado) VALUES (?, ?, ?, 0)');
            $stmt->execute([$hashed_token, $email, $validade]);

            // Enviar email com o token
            $mail = new PHPMailer(true);
            try {
                //Configurações do servidor
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;
                $mail->CharSet = 'UTF-8';

                //Destinatários
                $mail->setFrom(SMTP_USER, 'Sistema de Agenda');
                $mail->addAddress($email);

                //Conteúdo
                $mail->isHTML(true);
                $mail->Subject = 'Seu Token de Acesso - Sistema de Agenda';
                $mail->Body    = "Olá!<br><br>Obrigado por adquirir seu acesso ao nosso sistema de agenda.<br><br>Seu token de acesso é: <strong>" . $token . "</strong><br><br>Use este token para se registrar na plataforma.<br>Ele é válido por 30 dias e para um único uso.<br><br>Atenciosamente,<br>Equipe do Sistema de Agenda";
                $mail->AltBody = "Olá! Obrigado por adquirir seu acesso. Seu token é: " . $token;

                $mail->send();
                
                // Redirecionar para a página de obrigado
                $_SESSION['payment_success'] = true;
                $_SESSION['sent_token'] = $token;
                $_SESSION['sent_email'] = $email;
                header('Location: obrigado.php');
                exit;

            } catch (Exception $e) {
                // Se o e-mail falhar, ainda podemos mostrar o token ao usuário
                $_SESSION['payment_success'] = true;
                $_SESSION['payment_error'] = "Não foi possível enviar o e-mail com o token. Anote-o com segurança.";
                $_SESSION['sent_token'] = $token;
                $_SESSION['sent_email'] = $email;
                header('Location: obrigado.php');
                exit;
            }

        } catch (PDOException $e) {
            die("Erro no banco de dados: " . $e->getMessage());
        }
    } else {
        die("Endereço de e-mail inválido.");
    }
} else {
    header('Location: index.php');
    exit;
}
?>