<?php
$title = 'Rede de Apoio';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','psicologia','assistencia']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid   = (int)$_POST['occurrence_id'];
    $fdate = $_POST['followup_date'] ?: null;
    $org   = trim($_POST['organization'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $uid   = current_user()['id'];
    $stmt  = db()->prepare('INSERT INTO support_followups (occurrence_id,followup_date,organization,notes,created_by) VALUES (?,?,?,?,?)');
    $stmt->bind_param('isssi', $oid,$fdate,$org,$notes,$uid);
    $stmt->execute();
    audit_log('CREATE','support_network',"Rede de apoio ocorrência #$oid");
    flash('success','Acompanhamento registrado.');
    header('Location: /support_network.php'); exit;
}

$occ_list = db()->query('SELECT o.id,o.title,v.full_name FROM occurrences o JOIN victims v ON v.id=o.victim_id ORDER BY o.id DESC');
$list = db()->query(
    'SELECT s.*,o.title occ_title,v.full_name victim_name FROM support_followups s
     JOIN occurrences o ON o.id=s.occurrence_id JOIN victims v ON v.id=o.victim_id ORDER BY s.created_at DESC'
);
$show_form = ($_GET['action'] ?? '') === 'new';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Acompanhamentos da rede de apoio</h3>
  <a href="/support_network.php?action=new" class="btn btn-primary">+ Registrar acompanhamento</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Novo acompanhamento</div>
  <form method="POST" action="/support_network.php">
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
        <label>Data do acompanhamento</label>
        <input class="form-control" type="date" name="followup_date">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Organização / Entidade</label>
        <input class="form-control" name="organization" placeholder="CRAS, CREAS, ONG, etc.">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Observações</label>
        <textarea class="form-control" name="notes" rows="4"></textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/support_network.php" class="btn btn-secondary" style="margin-left:8px">Cancelar</a>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Histórico de acompanhamentos</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Vítima</th><th>Organização</th><th>Data</th><th>Observações</th></tr></thead>
      <tbody>
      <?php while ($row = $list->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)">#<?= $row['id'] ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><?= e($row['organization'] ?: '—') ?></td>
          <td style="font-size:12px"><?= $row['followup_date'] ? date('d/m/Y', strtotime($row['followup_date'])) : '—' ?></td>
          <td style="color:var(--muted);font-size:12px"><?= e(mb_strimwidth($row['notes'] ?? '', 0, 80, '...')) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
