<?php
$title = 'Gestão de Usuários';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = password_hash($_POST['password'] ?? 'nupro123', PASSWORD_BCRYPT);
    $role  = $_POST['role'] ?: 'pm';
    $stmt  = db()->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss', $name,$email,$pass,$role);
    $stmt->execute();
    audit_log('CREATE','users',"Usuário: $email");
    flash('success',"Usuário $name criado. Senha definida.");
    header('Location: /users.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'toggle') {
    $id  = (int)$_POST['user_id'];
    $cur = (int)$_POST['current_active'];
    $new = $cur ? 0 : 1;
    $stmt = db()->prepare('UPDATE users SET active=? WHERE id=?');
    $stmt->bind_param('ii', $new, $id);
    $stmt->execute();
    audit_log('UPDATE','users',"Toggle ativo user #$id → $new");
    flash('info','Status do usuário atualizado.');
    header('Location: /users.php'); exit;
}

$roles = ['admin','coordenacao','pm','gm','saude','psicologia','assistencia','diretoria'];
$users = db()->query('SELECT * FROM users ORDER BY created_at DESC');
$show_form = ($_GET['action'] ?? '') === 'new';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Usuários do sistema</h3>
  <a href="/users.php?action=new" class="btn btn-primary">+ Novo usuário</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Criar usuário</div>
  <form method="POST" action="/users.php">
    <input type="hidden" name="_action" value="create">
    <div class="grid-2">
      <div class="form-group">
        <label>Nome completo *</label>
        <input class="form-control" name="name" required>
      </div>
      <div class="form-group">
        <label>E-mail *</label>
        <input class="form-control" type="email" name="email" required>
      </div>
      <div class="form-group">
        <label>Senha inicial</label>
        <input class="form-control" type="password" name="password" placeholder="mínimo 8 caracteres">
      </div>
      <div class="form-group">
        <label>Perfil *</label>
        <select class="form-control" name="role" required>
          <?php foreach ($roles as $r): ?>
          <option value="<?= $r ?>"><?= role_label($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Criar usuário</button>
    <a href="/users.php" class="btn btn-secondary" style="margin-left:8px">Cancelar</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Lista de usuários</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Criado em</th><th>Ação</th></tr></thead>
      <tbody>
      <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)"><?= $u['id'] ?></td>
          <td><?= e($u['name']) ?></td>
          <td style="font-family:monospace;font-size:12px"><?= e($u['email']) ?></td>
          <td><span class="role-pill" style="display:inline-block;padding:2px 8px;border-radius:20px;background:rgba(232,54,93,.15);color:var(--accent);font-size:10px;font-weight:600"><?= e(role_label($u['role'])) ?></span></td>
          <td><?= $u['active'] ? '<span style="color:var(--green);font-size:12px">● Ativo</span>' : '<span style="color:var(--muted);font-size:12px">○ Inativo</span>' ?></td>
          <td style="font-size:12px;color:var(--muted)"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['id'] != current_user()['id']): ?>
            <form method="POST" action="/users.php" style="display:inline">
              <input type="hidden" name="_action" value="toggle">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="current_active" value="<?= $u['active'] ?>">
              <button type="submit" class="btn btn-secondary btn-sm"><?= $u['active'] ? 'Desativar' : 'Ativar' ?></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
