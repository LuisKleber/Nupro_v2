<?php
require_once __DIR__ . '/includes/config.php';

// Já autenticado → redireciona
if (!empty($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, name, password, role FROM users WHERE email = ? AND active = 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($pass, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_role'] = $row['role'];

        audit_log('LOGIN', 'index', 'Login realizado');
        header('Location: /dashboard.php');
        exit;
    }
  
    $error = 'E-mail ou senha inválidos.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NUPRO+ V2 — Acesso</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
  <style>
    :root { --bg:#0d0f14; --surface:#161b24; --border:#1e2535; --accent:#e8365d; --text:#e2e8f0; --muted:#64748b; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Sora', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; }

    .login-wrap {
      width: 100%; max-width: 420px; padding: 20px;
    }
    .logo-area { text-align: center; margin-bottom: 40px; }
    .logo-area .eyebrow { font-size: 10px; letter-spacing: 4px; color: var(--muted); text-transform: uppercase; margin-bottom: 8px; }
    .logo-area h1 { font-size: 48px; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: var(--text); }
    .logo-area h1 span { color: var(--accent); }
    .logo-area p { font-size: 13px; color: var(--muted); margin-top: 6px; }

    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
    .card h2 { font-size: 15px; font-weight: 600; margin-bottom: 24px; }

    .form-group { margin-bottom: 18px; }
    label { display: block; font-size: 11px; color: var(--muted); margin-bottom: 6px; letter-spacing: .5px; text-transform: uppercase; font-weight: 600; }
    input { width: 100%; padding: 11px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 14px; font-family: 'Sora', sans-serif; transition: border .15s; }
    input:focus { outline: none; border-color: var(--accent); }

    .btn { width: 100%; padding: 12px; background: var(--accent); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: .5px; transition: background .15s; }
    .btn:hover { background: #c42d50; }

    .error { background: rgba(232,54,93,.1); border: 1px solid rgba(232,54,93,.3); color: var(--accent); padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; }

    .hint { margin-top: 20px; font-size: 11px; color: var(--muted); text-align: center; line-height: 1.7; }
    .hint span { font-family: 'JetBrains Mono', monospace; background: var(--bg); padding: 1px 6px; border-radius: 4px; color: var(--text); }
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="logo-area">
    <p class="eyebrow">Núcleo de Proteção à Mulher</p>
    <h1>NUPRO<span>+</span></h1>
    <p>Sistema de Gestão — V2</p>
  </div>

  <div class="card">
    <h2>Acesso ao sistema</h2>

    <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/index.php">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn">Entrar</button>
    </form>

    <div class="hint">
      Usuários de demonstração:<br>
      <span>admin@nupro.local</span> · <span>pm@nupro.local</span><br>
      Senha: <span>nupro123</span>
    </div>
  </div>
</div>
</body>
</html>
