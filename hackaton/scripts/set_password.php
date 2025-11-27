<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require __DIR__ . '/../api/db.php'; // ajusta se necessário
if ($argc < 3) { echo "Uso: php set_password.php email senha\n"; exit(1); }
$email = $argv[1];
$senha = $argv[2];
if (!$pdo) { echo "Erro: sem conexão com DB\n"; exit(1); }
$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = :pw WHERE email = :email");
$stmt->execute([':pw'=>$senha, ':email'=>$email]);
if ($stmt->rowCount()) {
  echo "Senha atualizada para {$email}\n";
} else {
  echo "Usuário não encontrado ou senha inalterada\n";
}