<?php
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_GET['payment_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Payment ID não fornecido.']);
    exit;
}

$payment_id = $_GET['payment_id'];

// --- Conexão com o banco de dados de VENDAS ---
function pdo_connect_vendas() {
    try {
        return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    } catch (PDOException $exception) {
        exit('Falha ao conectar ao banco de dados de validação!');
    }
}

try {
    $pdo = pdo_connect_vendas();
    $stmt = $pdo->prepare('SELECT status FROM pix_payments WHERE mp_payment_id = ?');
    $stmt->execute([$payment_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['status' => $result['status']]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro de banco de dados.']);
}
