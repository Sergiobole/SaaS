<?php
// --- Configuração do Banco de Dados de Admin ---
// Mova para o config.php se preferir, mas por ser uma ferramenta separada, pode ficar aqui.
define('ADMIN_DB_HOST', 'localhost');
define('ADMIN_DB_NAME', 'agenda_adminacesso');
define('ADMIN_DB_USER', 'root'); // Use um usuário de DB dedicado para produção
define('ADMIN_DB_PASS', '');

// Função de conexão para o banco de dados de admin
function getAdminDbConnection() {
    $dsn = "mysql:host=" . ADMIN_DB_HOST . ";dbname=" . ADMIN_DB_NAME . ";charset=utf8";
    try {
        $pdo = new PDO($dsn, ADMIN_DB_USER, ADMIN_DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão com o banco de dados de administração: " . $e->getMessage());
    }
}

session_start();

// Lógica de Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_tool_logged_in']);
    header('Location: gerador_de_tokens.php');
    exit();
}

// Lógica de Login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $admin_pdo = getAdminDbConnection();
    $stmt = $admin_pdo->prepare("SELECT * FROM admin_acesso WHERE username = ?");
    $stmt->execute([$username]);
    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin_user && password_verify($password, $admin_user['password_hash'])) {
        $_SESSION['admin_tool_logged_in'] = true;
        header('Location: gerador_de_tokens.php');
        exit();
    } else {
        $login_error = 'Usuário ou senha incorretos!';
    }
}

// Se não estiver logado, mostra o formulário de login
if (!isset($_SESSION['admin_tool_logged_in']) || !$_SESSION['admin_tool_logged_in']) {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso Restrito - Gerador de Tokens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center">Acesso Restrito</h3>
                        <p class="text-center text-muted">Ferramenta de Administração</p>
                        <?php if ($login_error): ?><div class="alert alert-danger"><?php echo $login_error; ?></div><?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="login" value="true">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuário</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    exit(); // Encerra o script aqui se não estiver logado
}

// --- A partir daqui, o usuário está logado como admin ---
require_once 'src/database.php'; // Conexão para o banco da aplicação principal
require_once 'src/csrf.php';

$app_pdo = getDbConnection(); // Conexão com o banco de dados principal (agenda-saas)
$new_token_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_token'])) {
    validate_csrf_token($_POST['csrf_token']);
    $new_token = bin2hex(random_bytes(32));
    $duration = $_POST['duration_days'] ?? 30;

    $stmt = $app_pdo->prepare("INSERT INTO access_tokens (token, duration_days) VALUES (?, ?)");
    $stmt->execute([$new_token, $duration]);

    $_SESSION['new_token_message'] = "Token gerado com sucesso! Copie e envie para seu cliente: <strong>" . $new_token . "</strong>";
    header('Location: gerador_de_tokens.php');
    exit();
}

if (isset($_SESSION['new_token_message'])) {
    $new_token_message = $_SESSION['new_token_message'];
    unset($_SESSION['new_token_message']);
}

$tokens = $app_pdo->query("SELECT * FROM access_tokens ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerador de Tokens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1"><i class="bi bi-key-fill"></i> Gerador de Tokens</span>
    <a href="?logout=true" class="btn btn-sm btn-outline-light">Sair</a>
  </div>
</nav>
<div class="container mt-4">
    <?php if ($new_token_message): ?><div class="alert alert-success"><?php echo $new_token_message; ?></div><?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">Gerar Novo Token</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="generate_token" value="true">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="duration_days" class="form-label">Duração (dias)</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" value="30" required>
                    </div>
                    <div class="col-md-auto align-self-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Gerar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Tokens Gerados</div>
        <div class="card-body">
            <table class="table table-hover table-sm">
                <thead><tr><th>Token</th><th>Duração</th><th>Status</th><th>Data de Criação</th><th>Data de Uso</th></tr></thead>
                <tbody>
                <?php if (empty($tokens)): ?>
                    <tr><td colspan="5" class="text-center">Nenhum token gerado.</td></tr>
                <?php else: foreach ($tokens as $token): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($token['token']); ?></code></td>
                        <td><?php echo htmlspecialchars($token['duration_days']); ?> dias</td>
                        <td>
                            <?php if ($token['is_used']): ?>
                                <span class="badge bg-success">Utilizado (ID Usuário: <?php echo htmlspecialchars($token['used_by_user_id']); ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Disponível</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($token['created_at'])); ?></td>
                        <td><?php echo $token['used_at'] ? date('d/m/Y H:i', strtotime($token['used_at'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
