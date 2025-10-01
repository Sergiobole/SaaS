<?php
session_start();
require_once 'src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $business_name = $_POST['business_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validação simples
    if (empty($name) || empty($business_name) || empty($email) || empty($password)) {
        die('Por favor, preencha todos os campos.');
    }

    // Criptografa a senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $pdo = getDbConnection();

    // Verifica se o e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die('Este e-mail já está cadastrado. Tente outro.');
    }

    // Insere o novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (name, business_name, email, password) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$name, $business_name, $email, $hashed_password]);
        // Redireciona para o login após o sucesso
        header('Location: login.php?status=success');
        exit();
    } catch (PDOException $e) {
        die("Erro ao cadastrar: " . $e->getMessage());
    }

} else {
    header('Location: register.php');
    exit();
}
?>