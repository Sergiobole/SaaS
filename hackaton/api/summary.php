<?php
header('Content-Type: application/json; charset=utf-8');

// Tenta usar DB se disponível, senão devolve resposta fixa
require __DIR__.'/db.php';

$fallback = [
  'saldo' => 12450.30,
  'receitas' => 8750.00,
  'despesas' => 3420.15,
  'cashback' => 127.50,
  'categorias' => [
    ['label'=>'Alimentação','value'=>32.1,'color'=>'#f6a623'],
    ['label'=>'Transporte','value'=>24.5,'color'=>'#19b2c9'],
    ['label'=>'Compras','value'=>15.8,'color'=>'#1fbf6b'],
    ['label'=>'Saúde','value'=>11.3,'color'=>'#ff5c5c'],
    ['label'=>'Entretenimento','value'=>9.43,'color'=>'#7b61ff'],
    ['label'=>'Outros','value'=>6.79,'color'=>'#8a8f99']
  ],
  'recentes' => [
    ['categoria'=>'Alimentação','merchant'=>'iFood','loc'=>'iFood','hora'=>'15:30','amount'=>-45.90,'icon'=>'🍴','bg'=>'#fff4ea'],
    ['categoria'=>'Transporte','merchant'=>'Posto Shell','loc'=>'Posto Shell','hora'=>'08:15','amount'=>-120.00,'icon'=>'⛽','bg'=>'#eef7ff'],
    ['categoria'=>'Compras','merchant'=>'Amazon','loc'=>'Amazon','hora'=>'14:22','amount'=>-89.99,'icon'=>'🛒','bg'=>'#f0fff4'],
    ['categoria'=>'Salário','merchant'=>'Empresa X','loc'=>'Empresa X','hora'=>'09:00','amount'=>1500.00,'icon'=>'💼','bg'=>'#e8f7ff']
  ],
  'insights' => [
    'tip' => 'Você gastou 15% a mais em alimentação este mês. Considere usar cupons de desconto.',
    'goal' => 'Parabéns! Você economizou R$ 200 este mês conforme sua meta estabelecida.',
    'op' => 'Considere investir em CDB que está rendendo 13,2% ao ano.'
  ]
];

if($pdo){
  try{
    // Resumo receitas/despesas/saldo
    $row = $pdo->query("SELECT IFNULL(SUM(CASE WHEN amount>=0 THEN amount END),0) as receitas, IFNULL(SUM(CASE WHEN amount<0 THEN amount END),0) as despesas FROM transactions")->fetch();
    $receitas = (float)$row['receitas'];
    $despesas  = abs((float)$row['despesas']);
    $saldo = $receitas - $despesas;

    // categorias (percentual)
    $stmt = $pdo->query("SELECT c.name, c.color, IFNULL(ABS(SUM(t.amount)),0) as total FROM categories c LEFT JOIN transactions t ON t.category_id=c.id GROUP BY c.id ORDER BY total DESC");
    $cats = $stmt->fetchAll();
    $totalAll = array_sum(array_column($cats,'total')) ?: 1;
    $categorias = array_map(function($c) use($totalAll){
      return ['label'=>$c['name'],'value'=>round(($c['total']/$totalAll)*100,2),'color'=>$c['color']];
    }, $cats);

    // recentes
    $txs = $pdo->query("SELECT t.*, c.name as categoria FROM transactions t LEFT JOIN categories c ON c.id=t.category_id ORDER BY t.date DESC LIMIT 4")->fetchAll();
    $recentes = array_map(function($t){
      return [
        'categoria'=> $t['categoria'] ?? ($t['merchant'] ?? 'Transação'),
        'merchant' => $t['merchant'] ?? '',
        'loc'      => $t['merchant'] ?? '',
        'hora'     => date('H:i',strtotime($t['date'])),
        'amount'   => (float)$t['amount'],
        'icon'     => $t['icon'] ?? '💳',
        'bg'       => $t['color'] ?? '#eef2ff'
      ];
    }, $txs);

    // cashback
    $cb = $pdo->query("SELECT IFNULL(SUM(amount),0) as cb FROM cashback WHERE status='available'")->fetch();
    $cashback = (float)$cb['cb'];

    $response = [
      'saldo'=>$saldo,
      'receitas'=>$receitas,
      'despesas'=>$despesas,
      'categorias'=>$categorias,
      'recentes'=>$recentes,
      'cashback'=>$cashback,
      'insights'=>$fallback['insights']
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
  }catch(Exception $e){
    // em caso de erro no DB, devolve fallback
    echo json_encode($fallback, JSON_UNESCAPED_UNICODE);
    exit;
  }
}



// se não há conexão com DB, devolve fallback
echo json_encode($fallback, JSON_UNESCAPED_UNICODE);