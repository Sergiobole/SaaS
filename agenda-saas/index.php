<?php
session_start();

// Se o usuário já estiver logado, redireciona para o painel.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Se não, redireciona para a página de login.
header('Location: login.php');
exit();
?>