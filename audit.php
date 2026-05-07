<?php
$title = 'Trilha de Auditoria';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao']);

$logs = db()->query(
    'SELECT a.*, u.name user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id
     ORDER BY a.created_at DESC LIMIT 200'
);
$action_colors = [
    'LOGIN'  => '#10b981', 'LOGOUT' => '#64748b', 'CREATE' => '#3b82f6',
    'UPDATE' => '#f59e0b', 'DELETE' => '#e8365d',  'VIEW'   => '#8b5cf6'
];
?>
<div class="card">
  <div class="card-title">Log de auditoria — últimas 200 ações</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Usuário</th><th>Ação</th><th>Módulo</th><th>Descrição</th><th>IP</th><th>Data/Hora</th></tr></thead>
      <tbody>
      <?php while ($row = $logs->fetch_assoc()):
        $color = $action_colors[$row['action']] ?? '#64748b';
      ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)"><?= $row['id'] ?></td>
          <td><?= e($row['user_name'] ?? '—') ?></td>
          <td><span style="font-size:10px;padding:2px 8px;border-radius:20px;font-weight:700;letter-spacing:.5px;background:<?= $color ?>22;color:<?= $color ?>"><?= e($row['action']) ?></span></td>
          <td style="font-family:monospace;font-size:12px;color:var(--muted)"><?= e($row['module_name'] ?? '—') ?></td>
          <td style="font-size:12px"><?= e(mb_strimwidth($row['description'] ?? '', 0, 60, '...')) ?></td>
          <td style="font-family:monospace;font-size:11px;color:var(--muted)"><?= e($row['ip_address'] ?? '—') ?></td>
          <td style="font-size:11px;color:var(--muted)"><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
