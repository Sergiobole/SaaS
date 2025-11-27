<?php
header('Content-Type: application/json; charset=utf-8');

// Session cookie seguro
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

require __DIR__ . '/db.php';

function is_ajax_request() {
  $h = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  return strtolower($h) === 'xmlhttprequest' || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
}

function respond_json($data, $status = 200) {
  http_response_code($status);
  echo json_encode($data);
  exit;
}

function respond_redirect($url) {
  header('Location: ' . $url);
  exit;
}

if (!$pdo) {
  if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Erro de conexão ao banco','debug'=>'PDO null'],500);
  else respond_redirect('../login.html');
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
  if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Ação não informada'],400);
  else respond_redirect('../login.html');
}

// helpers
function fetch_user_by_email($pdo, $email) {
  $stmt = $pdo->prepare("SELECT id,name,email,password,phone,created_at FROM users WHERE email = :email LIMIT 1");
  $stmt->execute([':email'=>$email]);
  return $stmt->fetch();
}
function fetch_user_by_id($pdo, $id) {
  $stmt = $pdo->prepare("SELECT id,name,email,phone,created_at FROM users WHERE id = :id LIMIT 1");
  $stmt->execute([':id'=>$id]);
  return $stmt->fetch();
}
function sanitize_user($user) {
  if (!$user) return null;
  unset($user['password']);
  return $user;
}

// Parse input (form-data or JSON)
$raw = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$input = [];
if (stripos($contentType, 'application/json') !== false) {
  $raw = file_get_contents('php://input');
  $input = json_decode($raw, true) ?: [];
} else {
  $input = $_POST;
}

// DEBUG: retorna o que foi recebido
if ($action === 'debugRequest') {
  respond_json([
    'success' => true,
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'post' => $_POST,
    'input' => $input,
    'session_id' => session_id(),
    'session' => $_SESSION
  ]);
}

// REGISTER
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($input['name'] ?? '');
  $email = trim($input['email'] ?? '');
  $password = $input['password'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Nome, email e senha são obrigatórios'],400);
    else { $_SESSION['flash'] = 'Nome, email e senha são obrigatórios'; respond_redirect('../register.html'); }
  }

  if (fetch_user_by_email($pdo, $email)) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Email já cadastrado'],409);
    else { $_SESSION['flash'] = 'Email já cadastrado'; respond_redirect('../register.html'); }
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (name,email,password,phone,created_at,updated_at) VALUES (:name,:email,:password,'',NOW(),NOW())");
  try {
    $stmt->execute([':name'=>$name,':email'=>$email,':password'=>$hash]);
    $id = $pdo->lastInsertId();
    $user = fetch_user_by_id($pdo, $id);
    if (is_ajax_request()) respond_json(['success'=>true,'message'=>'Cadastro realizado','user'=>sanitize_user($user)],201);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    respond_redirect('../index.html');
  } catch (PDOException $e) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Erro ao cadastrar usuário','error'=>$e->getMessage()],500);
    else { $_SESSION['flash'] = 'Erro ao cadastrar usuário'; respond_redirect('../register.html'); }
  }
}

// LOGIN (melhorado com compatibilidade plaintext + hash)
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($input['email'] ?? '');
  $password = $input['password'] ?? '';

  if ($email === '' || $password === '') {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Email e senha são obrigatórios','debug_email'=>$email,'debug_pass'=>$password],400);
    else { $_SESSION['flash'] = 'Email e senha são obrigatórios'; respond_redirect('../login.html'); }
  }

  $user = fetch_user_by_email($pdo, $email);
  
  if (!$user) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Email ou senha incorretos'],401);
    else { $_SESSION['flash'] = 'Email ou senha incorretos'; respond_redirect('../login.html'); }
  }

  // Aceita tanto hash (password_verify) quanto plaintext (compatibilidade)
  $passwordOk = false;
  if (password_verify($password, $user['password'])) {
    $passwordOk = true;
  } elseif ($password === $user['password']) {
    // fallback: plaintext (compatibilidade)
    $passwordOk = true;
  }

  if (!$passwordOk) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Email ou senha incorretos'],401);
    else { $_SESSION['flash'] = 'Email ou senha incorretos'; respond_redirect('../login.html'); }
  }

  // Sucesso: criar sessão
  session_regenerate_id(true);
  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['user_email'] = $user['email'];
  
  if (is_ajax_request()) respond_json(['success'=>true,'message'=>'Autenticado','user'=>sanitize_user($user)]);
  else respond_redirect('../index.html');
}

// checkAuth
if ($action === 'checkAuth') {
  $resp = ['success'=>true,'authenticated'=>false,'user'=>null];
  if (isset($_SESSION['user_id'])) {
    $u = fetch_user_by_id($pdo, (int)$_SESSION['user_id']);
    if ($u) { 
      $resp['authenticated'] = true; 
      $resp['user'] = sanitize_user($u); 
    }
  }
  respond_json($resp);
}

// getUser
if ($action === 'getUser') {
  if (!isset($_SESSION['user_id'])) respond_json(['success'=>false,'message'=>'Não autenticado'],401);
  $u = fetch_user_by_id($pdo, (int)$_SESSION['user_id']);
  if (!$u) respond_json(['success'=>false,'message'=>'Usuário não encontrado'],404);
  respond_json(['success'=>true,'user'=>sanitize_user($u)]);
}

// LOGOUT
if ($action === 'logout') {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
  if (is_ajax_request()) respond_json(['success'=>true,'message'=>'Deslogado']);
  else respond_redirect('../login.html');
}

// updateProfile
if ($action === 'updateProfile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['user_id'])) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Não autenticado'],401);
    else respond_redirect('../login.html');
  }
  $name = trim($input['name'] ?? '');
  $email = trim($input['email'] ?? '');
  $phone = trim($input['phone'] ?? '');
  if ($name === '' || $email === '') {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Nome e email são obrigatórios'],400);
    else { $_SESSION['flash']='Nome e email são obrigatórios'; respond_redirect('../profile.html'); }
  }
  try {
    $stmt = $pdo->prepare("UPDATE users SET name=:name,email=:email,phone=:phone,updated_at=NOW() WHERE id = :id");
    $stmt->execute([':name'=>$name,':email'=>$email,':phone'=>$phone,':id'=>$_SESSION['user_id']]);
    $u = fetch_user_by_id($pdo, (int)$_SESSION['user_id']);
    if (is_ajax_request()) respond_json(['success'=>true,'message'=>'Perfil atualizado','user'=>sanitize_user($u)]);
    else { $_SESSION['flash']='Perfil atualizado'; respond_redirect('../profile.html'); }
  } catch (PDOException $e) {
    if (is_ajax_request()) respond_json(['success'=>false,'message'=>'Erro ao atualizar perfil'],500);
    else { $_SESSION['flash']='Erro ao atualizar perfil'; respond_redirect('../profile.html'); }
  }
}

respond_json(['success'=>false,'message'=>'Ação inválida'],400);
?>