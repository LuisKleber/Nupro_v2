<?php
$title = 'Vítimas';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','pm','gm','psicologia','assistencia']);

// POST — criar vítima
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $name      = trim($_POST['full_name'] ?? '');
    $cpf       = trim($_POST['cpf'] ?? '');
    $birth     = $_POST['birth_date'] ?: null;
    $addr_raw  = trim($_POST['address'] ?? '');
    $phone_raw = trim($_POST['phone'] ?? '');
    $conf      = isset($_POST['is_confidential']) ? 1 : 0;
    $uid       = current_user()['id'];

    $addr_enc  = $addr_raw  ? encrypt_data($addr_raw)  : null;
    $phone_enc = $phone_raw ? encrypt_data($phone_raw) : null;

    $stmt = db()->prepare(
        'INSERT INTO victims (full_name, cpf, birth_date, address_encrypted, phone_encrypted, is_confidential, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssii', $name, $cpf, $birth, $addr_enc, $phone_enc, $conf, $uid);
    $stmt->execute();
    audit_log('CREATE', 'victims', "Vítima: $name");
    flash('success', "Vítima \"$name\" cadastrada com sucesso.");
    header('Location: /victims.php'); exit;
}

// Listar
$victims = db()->query(
    'SELECT v.*, u.name created_by_name FROM victims v LEFT JOIN users u ON u.id = v.created_by ORDER BY v.created_at DESC'
);
$show_form = ($_GET['action'] ?? '') === 'new';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Cadastro de vítimas</h3>
  <a href="/victims.php?action=new" class="btn btn-primary">+ Nova vítima</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Cadastrar nova vítima</div>
  <form method="POST" action="/victims.php">
    <input type="hidden" name="_action" value="create">
    <div class="grid-2">
      <div class="form-group">
        <label>Nome completo *</label>
        <input class="form-control" name="full_name" required>
      </div>
      <div class="form-group">
        <label>CPF</label>
        <input class="form-control" name="cpf" placeholder="000.000.000-00">
      </div>
      <div class="form-group">
        <label>Data de nascimento</label>
        <input class="form-control" type="date" name="birth_date">
      </div>
      <div class="form-group">
        <label>Telefone (criptografado)</label>
        <input class="form-control" name="phone" placeholder="(11) 99999-9999">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Endereço (criptografado)</label>
        <input class="form-control" name="address" placeholder="Rua, número, bairro...">
      </div>
    </div>
    <div class="form-group">
      <label style="flex-direction:row;display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="is_confidential"> Cadastro confidencial (dados mascarados para perfis básicos)
      </label>
    </div>
    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-primary">Salvar vítima</button>
      <a href="/victims.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Vítimas cadastradas</div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Nome</th><th>CPF</th><th>Nasc.</th><th>Confidencial</th><th>Cadastrado por</th><th>Data</th></tr>
      </thead>
      <tbody>
      <?php while ($v = $victims->fetch_assoc()):
        $masked = $v['is_confidential'] && !in_array($user['role'], ['admin','coordenacao']);
      ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)"><?= e($v['id']) ?></td>
          <td><?= $masked ? '<em style="color:var(--muted)">*** Confidencial ***</em>' : e($v['full_name']) ?></td>
          <td style="font-family:monospace"><?= $masked ? '***' : e($v['cpf'] ?: '—') ?></td>
          <td><?= $v['birth_date'] ? date('d/m/Y', strtotime($v['birth_date'])) : '—' ?></td>
          <td><?= $v['is_confidential'] ? '<span style="color:var(--yellow)">🔒 Sim</span>' : '—' ?></td>
          <td><?= e($v['created_by_name'] ?? '—') ?></td>
          <td style="color:var(--muted);font-size:12px"><?= date('d/m/Y', strtotime($v['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
