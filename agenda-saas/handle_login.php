<?php
session_start();
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=Preencha todos os campos');
        exit();
    }

    $pdo = getDbConnection();

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['business_name'] = $user['business_name'];
        
        header('Location:dashboard.php');
        exit();
    } else {
        // Falha no login
        header('Location:login.php?error=Email ou senha inválidos');
        exit();
    }
} else {
    header('Location:login.php');
    exit();
}
?>