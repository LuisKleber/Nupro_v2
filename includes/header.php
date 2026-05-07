<?php
// includes/header.php
require_once __DIR__ . '/config.php';

require_login();
$user  = current_user();
$flash = get_flash();
$page  = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:      #0d0f14;
      --surface: #161b24;
      --border:  #1e2535;
      --accent:  #e8365d;
      --accent2: #ff7043;
      --blue:    #3b82f6;
      --green:   #10b981;
      --yellow:  #f59e0b;
      --text:    #e2e8f0;
      --muted:   #64748b;
      --sidebar: 240px;
      --radius:  10px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Sora', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

    /* ── Sidebar ── */
    .sidebar {
      width: var(--sidebar); background: var(--surface); border-right: 1px solid var(--border);
      display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100;
    }
    .sidebar-logo {
      padding: 24px 20px 20px; border-bottom: 1px solid var(--border);
    }
    .sidebar-logo .badge {
      font-size: 9px; font-weight: 700; letter-spacing: 3px; color: var(--accent);
      text-transform: uppercase; display: block; margin-bottom: 4px;
    }
    .sidebar-logo h1 { font-size: 22px; font-weight: 700; color: var(--text); }
    .sidebar-logo h1 span { color: var(--accent); }

    .sidebar-nav { padding: 16px 0; flex: 1; overflow-y: auto; }
    .nav-section { padding: 8px 20px 4px; font-size: 9px; letter-spacing: 2px; color: var(--muted); text-transform: uppercase; }
    .nav-link {
      display: flex; align-items: center; gap: 10px; padding: 10px 20px;
      color: var(--muted); text-decoration: none; font-size: 13px; font-weight: 400;
      transition: all .15s; border-left: 3px solid transparent;
    }
    .nav-link:hover { color: var(--text); background: rgba(255,255,255,.04); }
    .nav-link.active { color: var(--text); border-left-color: var(--accent); background: rgba(232,54,93,.06); }
    .nav-link .icon { font-size: 15px; width: 18px; text-align: center; }

    .sidebar-user {
      padding: 16px 20px; border-top: 1px solid var(--border);
      font-size: 12px; color: var(--muted);
    }
    .sidebar-user strong { display: block; color: var(--text); font-size: 13px; margin-bottom: 2px; }
    .sidebar-user .role-pill {
      display: inline-block; padding: 2px 8px; border-radius: 20px;
      background: rgba(232,54,93,.15); color: var(--accent); font-size: 10px; font-weight: 600; letter-spacing: .5px;
    }
    .logout-btn {
      display: block; margin: 10px 0 0; padding: 7px 12px; background: transparent;
      border: 1px solid var(--border); border-radius: var(--radius); color: var(--muted);
      text-decoration: none; font-size: 11px; text-align: center; transition: all .15s;
    }
    .logout-btn:hover { border-color: var(--accent); color: var(--accent); }

    /* ── Main ── */
    .main { margin-left: var(--sidebar); flex: 1; display: flex; flex-direction: column; }
    .topbar {
      padding: 18px 30px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .topbar h2 { font-size: 18px; font-weight: 600; }
    .topbar-meta { font-size: 12px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }

    .content { padding: 30px; flex: 1; }

    /* ── Flash ── */
    .flash {
      padding: 12px 18px; border-radius: var(--radius); margin-bottom: 24px;
      font-size: 13px; display: flex; align-items: center; gap: 10px;
    }
    .flash.success { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.3); color: var(--green); }
    .flash.error   { background: rgba(232,54,93,.12);  border: 1px solid rgba(232,54,93,.3);  color: var(--accent); }
    .flash.info    { background: rgba(59,130,246,.12); border: 1px solid rgba(59,130,246,.3); color: var(--blue); }

    /* ── Cards ── */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; }
    .card-title { font-size: 13px; font-weight: 600; letter-spacing: .5px; color: var(--muted); text-transform: uppercase; margin-bottom: 16px; }

    /* ── Table ── */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 10px 14px; color: var(--muted); font-size: 11px; letter-spacing: 1px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
    td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,.04); }
    tr:hover td { background: rgba(255,255,255,.02); }

    /* ── Badges ── */
    .badge-risk { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .5px; }
    .badge-risk.A { background: rgba(232,54,93,.2); color: var(--accent); }
    .badge-risk.B { background: rgba(245,158,11,.2); color: var(--yellow); }
    .badge-risk.C { background: rgba(16,185,129,.2); color: var(--green); }

    /* ── Buttons ── */
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius); font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all .15s; }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-primary:hover { background: #c42d50; }
    .btn-secondary { background: var(--border); color: var(--text); }
    .btn-secondary:hover { background: #2a3348; }
    .btn-outline { background: transparent; color: var(--accent); border: 1px solid var(--accent); }
    .btn-outline:hover { background: rgba(232,54,93,.1); }
    .btn-sm { padding: 5px 10px; font-size: 11px; }

    /* ── Forms ── */
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 6px; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; }
    .form-control {
      width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text); font-size: 13px; font-family: 'Sora', sans-serif;
      transition: border .15s;
    }
    .form-control:focus { outline: none; border-color: var(--accent); }
    select.form-control option { background: var(--surface); }

    /* ── Grid ── */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

    /* ── KPI Cards ── */
    .kpi { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 24px; }
    .kpi-label { font-size: 11px; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
    .kpi-value { font-size: 32px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .kpi-value.red    { color: var(--accent); }
    .kpi-value.yellow { color: var(--yellow); }
    .kpi-value.green  { color: var(--green); }
    .kpi-value.blue   { color: var(--blue); }

    /* ── Panic banner ── */
    .panic-banner {
      background: rgba(232,54,93,.15); border: 1px solid var(--accent);
      border-radius: var(--radius); padding: 14px 20px; margin-bottom: 24px;
      display: flex; align-items: center; gap: 12px;
      animation: pulse-border 1.5s ease-in-out infinite;
    }
    @keyframes pulse-border {
      0%,100% { border-color: var(--accent); }
      50%      { border-color: transparent; }
    }

    /* ── Map picker ── */
    .map-search-bar { display: flex; gap: 8px; margin-bottom: 10px; }
    .map-search-bar input { flex: 1; }
    .map-search-bar button { flex-shrink: 0; }
    .map-coords-info {
      margin-top: 8px; padding: 8px 12px; background: rgba(59,130,246,.1);
      border: 1px solid rgba(59,130,246,.3); border-radius: var(--radius);
      font-size: 12px; color: var(--blue); font-family: 'JetBrains Mono', monospace;
      display: none;
    }

    @media (max-width: 900px) {
      .sidebar { width: 60px; }
      .sidebar-logo h1, .nav-link span, .sidebar-user strong, .sidebar-user .role-pill, .logout-btn { display: none; }
      .main { margin-left: 60px; }
      .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="badge">Sistema</span>
    <h1>NUPRO<span>+</span></h1>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="/dashboard.php"   class="nav-link <?= $page==='dashboard'   ?'active':'' ?>"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="/map.php"         class="nav-link <?= $page==='map'         ?'active':'' ?>"><span class="icon">🗺️</span><span>Mapa</span></a>

    <div class="nav-section">Registros</div>
    <a href="/victims.php"     class="nav-link <?= $page==='victims'     ?'active':'' ?>"><span class="icon">👤</span><span>Vítimas</span></a>
    <a href="/occurrences.php" class="nav-link <?= $page==='occurrences' ?'active':'' ?>"><span class="icon">📋</span><span>Ocorrências</span></a>
    <a href="/protective_measures.php" class="nav-link <?= $page==='protective_measures'?'active':'' ?>"><span class="icon">🛡️</span><span>Medidas Protetivas</span></a>
    <a href="/health_visits.php" class="nav-link <?= $page==='health_visits'?'active':'' ?>"><span class="icon">🏥</span><span>Saúde</span></a>
    <a href="/support_network.php" class="nav-link <?= $page==='support_network'?'active':'' ?>"><span class="icon">🤝</span><span>Rede de Apoio</span></a>

    <div class="nav-section">Emergência</div>
    <a href="/panic_button.php" class="nav-link <?= $page==='panic_button'?'active':'' ?>"><span class="icon">🚨</span><span>Botão de Pânico</span></a>

    <?php if (in_array($user['role'], ['admin','coordenacao'])): ?>
    <div class="nav-section">Admin</div>
    <a href="/users.php"  class="nav-link <?= $page==='users' ?'active':'' ?>"><span class="icon">👥</span><span>Usuários</span></a>
    <a href="/audit.php"  class="nav-link <?= $page==='audit' ?'active':'' ?>"><span class="icon">📜</span><span>Auditoria</span></a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-user">
    <strong><?= e($user['name']) ?></strong>
    <span class="role-pill"><?= e(role_label($user['role'])) ?></span>
    <a href="/logout.php" class="logout-btn">Sair</a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h2><?= e($title ?? APP_NAME) ?></h2>
    <span class="topbar-meta"><?= date('d/m/Y H:i') ?></span>
  </div>
  <div class="content">

<?php if ($flash): ?>
  <div class="flash <?= e($flash['type']) ?>">
    <?= $flash['type']==='success' ? '✅' : ($flash['type']==='error' ? '❌' : 'ℹ️') ?>
    <?= e($flash['msg']) ?>
  </div>
<?php endif; ?>
