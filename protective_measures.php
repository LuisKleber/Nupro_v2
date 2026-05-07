<?php
$title = 'Medidas Protetivas';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','pm','gm','assistencia']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $oid    = (int)$_POST['occurrence_id'];
    $type   = trim($_POST['measure_type'] ?? '');
    $issued = $_POST['issued_date'] ?: null;
    $expiry = $_POST['expiry_date'] ?: null;
    $status = $_POST['status'] ?: 'ativa';
    $breach = isset($_POST['breach_reported']) ? 1 : 0;
    $notes  = trim($_POST['notes'] ?? '');
    $uid    = current_user()['id'];

    $s2 = db()->prepare(
        'INSERT INTO protective_measures (occurrence_id,measure_type,issued_date,expiry_date,status,breach_reported,notes,created_by)
         VALUES (?,?,?,?,?,?,?,?)'
    );
    $s2->bind_param('issssisi', $oid,$type,$issued,$expiry,$status,$breach,$notes,$uid);
    $s2->execute();
    audit_log('CREATE','protective_measures',"Medida: $type");
    flash('success','Medida protetiva registrada.');
    header('Location: /protective_measures.php'); exit;
}

$occ_list = db()->query('SELECT o.id, o.title, v.full_name FROM occurrences o JOIN victims v ON v.id=o.victim_id ORDER BY o.id DESC');

$list = db()->query(
    "SELECT m.*, o.title occ_title, v.full_name victim_name
     FROM protective_measures m
     JOIN occurrences o ON o.id=m.occurrence_id
     JOIN victims v ON v.id=o.victim_id
     ORDER BY m.breach_reported DESC, m.created_at DESC"
);
$show_form = ($_GET['action'] ?? '') === 'new';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Controle de medidas protetivas</h3>
  <a href="/protective_measures.php?action=new" class="btn btn-primary">+ Nova medida</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Registrar medida protetiva</div>
  <form method="POST" action="/protective_measures.php">
    <input type="hidden" name="_action" value="create">
    <div class="grid-2">
      <div class="form-group">
        <label>Ocorrência vinculada *</label>
        <select class="form-control" name="occurrence_id" required>
          <option value="">Selecione...</option>
          <?php while ($o = $occ_list->fetch_assoc()): ?>
          <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> — <?= e($o['title']) ?> (<?= e($o['full_name']) ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tipo de medida</label>
        <input class="form-control" name="measure_type" placeholder="Ex: Afastamento do lar...">
      </div>
      <div class="form-group">
        <label>Data de emissão</label>
        <input class="form-control" type="date" name="issued_date">
      </div>
      <div class="form-group">
        <label>Data de expiração</label>
        <input class="form-control" type="date" name="expiry_date">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select class="form-control" name="status">
          <option value="ativa">Ativa</option>
          <option value="expirada">Expirada</option>
          <option value="revogada">Revogada</option>
        </select>
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end">
        <label style="flex-direction:row;display:flex;align-items:center;gap:8px;cursor:pointer;margin:0">
          <input type="checkbox" name="breach_reported"> <span>Descumprimento reportado</span>
        </label>
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Observações</label>
        <textarea class="form-control" name="notes" rows="3"></textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/protective_measures.php" class="btn btn-secondary" style="margin-left:8px">Cancelar</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Medidas protetivas</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Vítima</th><th>Tipo</th><th>Status</th><th>Validade</th><th>Descumprimento</th></tr></thead>
      <tbody>
      <?php while ($row = $list->fetch_assoc()): ?>
        <tr <?= $row['breach_reported'] ? 'style="background:rgba(232,54,93,.06)"' : '' ?>>
          <td style="font-family:monospace;color:var(--muted)">#<?= $row['id'] ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><?= e($row['measure_type'] ?: '—') ?></td>
          <td>
            <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:<?= $row['status']==='ativa'?'rgba(16,185,129,.2)':'rgba(100,116,139,.2)' ?>;color:<?= $row['status']==='ativa'?'var(--green)':'var(--muted)' ?>">
              <?= e(ucfirst($row['status'])) ?>
            </span>
          </td>
          <td style="font-size:12px"><?= $row['expiry_date'] ? date('d/m/Y', strtotime($row['expiry_date'])) : '—' ?></td>
          <td><?= $row['breach_reported'] ? '<span style="color:var(--accent);font-weight:700">⚠️ Sim</span>' : '<span style="color:var(--muted)">Não</span>' ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
