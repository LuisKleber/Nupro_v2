<?php
$title = '🚨 Botão de Pânico';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','pm','gm']);

// POST — registrar alerta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $vid  = (int)$_POST['victim_id'];
    $loc  = trim($_POST['location'] ?? '');
    $lat  = $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null;
    $lng  = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
    $uid  = current_user()['id'];
    $stmt = db()->prepare('INSERT INTO panic_alerts (victim_id,location,latitude,longitude,triggered_by) VALUES (?,?,?,?,?)');
    $stmt->bind_param('issdi', $vid,$loc,$lat,$lng,$uid);
    $stmt->execute();
    audit_log('CREATE','panic_button',"Alerta para vítima #$vid");
    flash('success','Alerta de pânico registrado!');
    header('Location: /panic_button.php'); exit;
}

// POST — resolver alerta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'resolve') {
    $stmt = db()->prepare('UPDATE panic_alerts SET resolved=1 WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    audit_log('UPDATE','panic_button',"Alerta #$id resolvido");
    flash('info','Alerta marcado como resolvido.');
    header('Location: /panic_button.php'); exit;
}

// Vítimas
$victims_list = db()->query('SELECT id, full_name FROM victims ORDER BY full_name');

// Alertas ativos
$open = db()->query(
    "SELECT p.*, v.full_name victim_name, u.name agent_name
     FROM panic_alerts p JOIN victims v ON v.id=p.victim_id LEFT JOIN users u ON u.id=p.triggered_by
     WHERE p.resolved=0 ORDER BY p.created_at DESC"
);
// Alertas resolvidos (últimos 5)
$closed = db()->query(
    "SELECT p.*, v.full_name victim_name FROM panic_alerts p JOIN victims v ON v.id=p.victim_id
     WHERE p.resolved=1 ORDER BY p.created_at DESC LIMIT 5"
);
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Registro de alertas de emergência</h3>
  <a href="/panic_button.php?action=new" class="btn btn-primary" style="background:var(--accent)">🚨 Acionar alerta</a>
</div>

<?php if (($_GET['action'] ?? '') === 'new'): ?>
<div class="card" style="margin-bottom:24px;border-color:rgba(232,54,93,.4)">
  <div class="card-title" style="color:var(--accent)">⚠️ Registrar alerta de pânico</div>
  <form method="POST" action="/panic_button.php">
    <input type="hidden" name="_action" value="create">
    <div class="grid-2">
      <div class="form-group">
        <label>Vítima *</label>
        <select class="form-control" name="victim_id" required>
          <option value="">Selecione...</option>
          <?php while ($v = $victims_list->fetch_assoc()): ?>
          <option value="<?= $v['id'] ?>"><?= e($v['full_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Localização</label>
        <input class="form-control" name="location" placeholder="Endereço ou descrição do local">
      </div>
      <div class="form-group">
        <label>Latitude</label>
        <input class="form-control" name="latitude" type="number" step="any">
      </div>
      <div class="form-group">
        <label>Longitude</label>
        <input class="form-control" name="longitude" type="number" step="any">
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="background:var(--accent)">🚨 Confirmar alerta</button>
    <a href="/panic_button.php" class="btn btn-secondary" style="margin-left:8px">Cancelar</a>
  </form>
</div>
<?php endif; ?>

<div class="card" style="margin-bottom:24px;border-color:rgba(232,54,93,.3)">
  <div class="card-title" style="color:var(--accent)">Alertas ativos</div>
  <?php $open_count = 0; ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Vítima</th><th>Localização</th><th>Acionado por</th><th>Data/Hora</th><th>Ação</th></tr></thead>
      <tbody>
      <?php while ($row = $open->fetch_assoc()): $open_count++; ?>
        <tr>
          <td style="font-family:monospace;color:var(--accent)">#<?= e($row['id']) ?></td>
          <td><strong><?= e($row['victim_name']) ?></strong></td>
          <td><?= e($row['location'] ?: '—') ?></td>
          <td><?= e($row['agent_name'] ?? '—') ?></td>
          <td style="font-size:12px"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
          <td>
            <form method="POST" action="/panic_button.php" style="display:inline">
              <input type="hidden" name="_action" value="resolve">
              <input type="hidden" name="alert_id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-secondary btn-sm">✔ Resolver</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($open_count === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">✅ Nenhum alerta ativo no momento</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-title">Últimos alertas resolvidos</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Vítima</th><th>Localização</th><th>Data</th></tr></thead>
      <tbody>
      <?php while ($row = $closed->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)">#<?= e($row['id']) ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><?= e($row['location'] ?: '—') ?></td>
          <td style="font-size:12px;color:var(--muted)"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
