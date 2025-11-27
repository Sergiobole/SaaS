<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'Não autenticado']);
  exit;
}
if (!$pdo) {
  http_response_code(500);
  echo json_encode(['error'=>'Erro de conexão']);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
  // receitas/despesas
  $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN amount>=0 THEN amount END),0) as receitas, COALESCE(SUM(CASE WHEN amount<0 THEN amount END),0) as despesas FROM transactions WHERE user_id = :uid");
  $stmt->execute([':uid'=>$user_id]);
  $row = $stmt->fetch();
  $receitas = (float)$row['receitas'];
  $despesas = abs((float)$row['despesas']);
  $saldo = $receitas - $despesas;

  // categorias
  $stmt = $pdo->prepare("SELECT c.name, c.color, COALESCE(ABS(SUM(t.amount)),0) as total FROM categories c LEFT JOIN transactions t ON t.category_id = c.id AND t.user_id = :uid WHERE c.user_id = :uid GROUP BY c.id ORDER BY total DESC");
  $stmt->execute([':uid'=>$user_id]);
  $cats = $stmt->fetchAll();
  $totalAll = array_sum(array_column($cats,'total')) ?: 1;
  $categorias = array_map(function($c) use($totalAll){
    return ['label'=>$c['name'],'value'=> round(($c['total']/$totalAll)*100,2),'color'=>$c['color']];
  }, $cats);

  // recentes
  $stmt = $pdo->prepare("SELECT t.*, c.name as categoria FROM transactions t LEFT JOIN categories c ON c.id = t.category_id WHERE t.user_id = :uid ORDER BY t.date DESC LIMIT 6");
  $stmt->execute([':uid'=>$user_id]);
  $txs = $stmt->fetchAll();
  $recentes = array_map(function($t){ return [
    'categoria' => $t['categoria'] ?? ($t['merchant'] ?? 'Transação'),
    'merchant' => $t['merchant'] ?? '',
    'loc' => $t['merchant'] ?? '',
    'hora' => date('H:i', strtotime($t['date'])),
    'amount' => (float)$t['amount'],
    'icon' => $t['icon'] ?? '💳',
    'bg' => $t['color'] ?? '#eef2ff'
  ]; }, $txs);

  // cashback
  $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as cb FROM cashback WHERE user_id = :uid AND status = 'available'");
  $stmt->execute([':uid'=>$user_id]);
  $cb = $stmt->fetch();
  $cashback = (float)($cb['cb'] ?? 0);

  // insights (simples)
  $insights = [
    'tip' => 'Você gastou mais em Alimentação este mês. Reveja seus últimos pedidos.',
    'goal' => 'Meta de economia ativa: R$ 500/mês.',
    'op' => 'Considere a conta poupança automática.'
  ];

  echo json_encode([
    'saldo'=>$saldo,
    'receitas'=>$receitas,
    'despesas'=>$despesas,
    'cashback'=>$cashback,
    'categorias'=>$categorias,
    'recentes'=>$recentes,
    'insights'=>$insights,
    // métricas de variação (exemplo placeholder)
    'saldo_var'=>2.5,'receitas_var'=>5.2,'despesas_var'=>1.8
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  error_log('dashboard error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['error'=>'Erro ao carregar dashboard']);
}