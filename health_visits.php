<?php
// health_visits.php
$title = 'Atendimentos de Saúde';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','saude']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid    = (int)$_POST['occurrence_id'];
    $vdate  = $_POST['visit_date'] ?: null;
    $unit   = trim($_POST['unit_name'] ?? '');
    $report = trim($_POST['report'] ?? '');
    $uid    = current_user()['id'];
    $stmt   = db()->prepare('INSERT INTO health_visits (occurrence_id,visit_date,unit_name,report,created_by) VALUES (?,?,?,?,?)');
    $stmt->bind_param('isssi', $oid,$vdate,$unit,$report,$uid);
    $stmt->execute();
    audit_log('CREATE','health_visits',"Atend. saúde ocorrência #$oid");
    flash('success','Atendimento de saúde registrado.');
    header('Location: /health_visits.php'); exit;
}

$occ_list = db()->query('SELECT o.id,o.title,v.full_name FROM occurrences o JOIN victims v ON v.id=o.victim_id ORDER BY o.id DESC');
$list = db()->query(
    'SELECT h.*,o.title occ_title,v.full_name victim_name FROM health_visits h
     JOIN occurrences o ON o.id=h.occurrence_id JOIN victims v ON v.id=o.victim_id ORDER BY h.created_at DESC'
);
$show_form = ($_GET['action'] ?? '') === 'new';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Atendimentos de saúde</h3>
  <a href="/health_visits.php?action=new" class="btn btn-primary">+ Registrar atendimento</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Novo atendimento</div>
  <form method="POST" action="/health_visits.php">
    <div class="grid-2">
      <div class="form-group">
        <label>Ocorrência</label>
        <select class="form-control" name="occurrence_id" required>
          <option value="">Selecione...</option>
          <?php while ($o = $occ_list->fetch_assoc()): ?>
          <option value="<?= $o['id'] ?>">#<?= $o['id'] ?> — <?= e($o['full_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Data do atendimento</label>
        <input class="form-control" type="date" name="visit_date">
      </div>
      <div class="form-group">
        <label>Unidade de saúde</label>
        <input class="form-control" name="unit_name" placeholder="UBS, UPA, Hospital...">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Relatório / Observações</label>
        <textarea class="form-control" name="report" rows="4"></textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/health_visits.php" class="btn btn-secondary" style="margin-left:8px">Cancelar</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Histórico de atendimentos</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Vítima</th><th>Unidade</th><th>Data</th><th>Relatório</th></tr></thead>
      <tbody>
      <?php while ($row = $list->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)">#<?= $row['id'] ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><?= e($row['unit_name'] ?: '—') ?></td>
          <td style="font-size:12px"><?= $row['visit_date'] ? date('d/m/Y', strtotime($row['visit_date'])) : '—' ?></td>
          <td style="color:var(--muted);font-size:12px"><?= e(mb_strimwidth($row['report'] ?? '', 0, 80, '...')) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
