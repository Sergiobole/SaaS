<?php
// Configurar conforme seu ambiente (XAMPP padrão)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'financehub';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHAR = 'utf8mb4';

$pdo = null;
try {
  $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHAR}";
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  // Em dev, logue se desejar: error_log($e->getMessage());
  $pdo = null;
}
?>
<?php
// Conexão PDO centralizada - ajuste credenciais conforme seu ambiente XAMPP
$cfg = [
  'host' => '127.0.0.1',
  'db'   => 'financehub',
  'user' => 'root',
  'pass' => ''
];

$pdo = null;
try {
  $pdo = new PDO(
    "mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4",
    $cfg['user'],
    $cfg['pass'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  );
} catch (Exception $e) {
  // registra erro para debug (não exibe ao usuário)
  error_log('DB connect error: ' . $e->getMessage());
  $pdo = null;
}