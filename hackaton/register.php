<?php
session_start();
if (!empty($_SESSION['flash'])) {
  echo '<p style="color:red;">' . htmlspecialchars($_SESSION['flash']) . '</p>';
  unset($_SESSION['flash']);
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Registrar</title></head>
<body>
  <form id="registerForm" method="post" action="api/auth.php?action=register">
    <label>Nome: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Senha: <input type="password" name="password" required></label><br>
    <button type="submit">Registrar</button>
  </form>
  <p><a href="login.html">Voltar ao login</a></p>
</body>
</html>