<?php
$title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// KPIs
$kpis = [];
$kpis['total_victims']     = db()->query('SELECT COUNT(*) c FROM victims')->fetch_assoc()['c'];
$kpis['total_occurrences'] = db()->query('SELECT COUNT(*) c FROM occurrences')->fetch_assoc()['c'];
$kpis['risk_a']            = db()->query("SELECT COUNT(*) c FROM occurrences WHERE risk_level='A'")->fetch_assoc()['c'];
$kpis['breach']            = db()->query("SELECT COUNT(*) c FROM protective_measures WHERE breach_reported=1")->fetch_assoc()['c'];
$kpis['panic_open']        = db()->query("SELECT COUNT(*) c FROM panic_alerts WHERE resolved=0")->fetch_assoc()['c'];
$kpis['measures_active']   = db()->query("SELECT COUNT(*) c FROM protective_measures WHERE status='ativa'")->fetch_assoc()['c'];

// Panico aberto?
$panic_open = $kpis['panic_open'] > 0;

// Ocorrências por risco (para gráfico)
$risk_data = [];
$res = db()->query("SELECT risk_level, COUNT(*) cnt FROM occurrences GROUP BY risk_level");
while ($r = $res->fetch_assoc()) $risk_data[$r['risk_level']] = (int)$r['cnt'];

// Ocorrências por mês (últimos 6 meses)
$month_data = [];
$res = db()->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) cnt FROM occurrences
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym"
);
while ($r = $res->fetch_assoc()) $month_data[] = $r;

// Últimas ocorrências
$recent = db()->query(
    "SELECT o.id, o.title, o.risk_level, o.created_at, v.full_name victim_name, u.name agent_name
     FROM occurrences o
     JOIN victims v ON v.id = o.victim_id
     LEFT JOIN users u ON u.id = o.created_by
     ORDER BY o.created_at DESC LIMIT 5"
);
?>

<?php if ($panic_open): ?>
<div class="panic-banner">
  🚨 <strong>Alerta de pânico ativo!</strong> — Há <?= (int)$kpis['panic_open'] ?> alerta(s) de emergência não resolvido(s).
  <a href="/panic_button.php" class="btn btn-primary btn-sm" style="margin-left:auto">Ver alertas</a>
</div>
<?php endif; ?>

<div class="grid-4" style="margin-bottom:24px">
  <div class="kpi"><div class="kpi-label">Vítimas cadastradas</div><div class="kpi-value blue"><?= $kpis['total_victims'] ?></div></div>
  <div class="kpi"><div class="kpi-label">Ocorrências</div><div class="kpi-value"><?= $kpis['total_occurrences'] ?></div></div>
  <div class="kpi"><div class="kpi-label">Risco Alto (A)</div><div class="kpi-value red"><?= $kpis['risk_a'] ?></div></div>
  <div class="kpi"><div class="kpi-label">Alertas de pânico</div><div class="kpi-value <?= $panic_open ? 'red' : 'green' ?>"><?= $kpis['panic_open'] ?></div></div>
</div>

<div class="grid-2" style="margin-bottom:24px">
  <div class="card">
    <div class="card-title">Ocorrências por nível de risco</div>
    <canvas id="chartRisk" height="180"></canvas>
  </div>
  <div class="card">
    <div class="card-title">Ocorrências por mês</div>
    <canvas id="chartMonth" height="180"></canvas>
  </div>
</div>

<div class="card">
  <div class="card-title">Últimas ocorrências</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Título</th><th>Vítima</th><th>Risco</th><th>Agente</th><th>Data</th></tr></thead>
      <tbody>
      <?php while ($row = $recent->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)">#<?= e($row['id']) ?></td>
          <td><?= e($row['title']) ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><span class="badge-risk <?= e($row['risk_level']) ?>"><?= risk_label($row['risk_level']) ?></span></td>
          <td><?= e($row['agent_name'] ?? '—') ?></td>
          <td style="color:var(--muted);font-size:12px"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
  plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Sora' } } } },
  scales: {
    x: { ticks: { color: '#64748b' }, grid: { color: '#1e2535' } },
    y: { ticks: { color: '#64748b' }, grid: { color: '#1e2535' }, beginAtZero: true }
  }
};

// Risco
new Chart(document.getElementById('chartRisk'), {
  type: 'doughnut',
  data: {
    labels: ['Alto (A)', 'Médio (B)', 'Baixo (C)'],
    datasets: [{ data: [<?= (int)($risk_data['A']??0) ?>, <?= (int)($risk_data['B']??0) ?>, <?= (int)($risk_data['C']??0) ?>],
      backgroundColor: ['#e8365d', '#f59e0b', '#10b981'], borderWidth: 0 }]
  },
  options: { plugins: { legend: { labels: { color: '#94a3b8' } } }, cutout: '65%' }
});

// Mensal
const monthLabels = [<?= implode(',', array_map(fn($r) => '"'.e($r['ym']).'"', $month_data)) ?>];
const monthValues = [<?= implode(',', array_map(fn($r) => (int)$r['cnt'], $month_data)) ?>];

new Chart(document.getElementById('chartMonth'), {
  type: 'bar',
  data: {
    labels: monthLabels,
    datasets: [{ label: 'Ocorrências', data: monthValues,
      backgroundColor: 'rgba(232,54,93,.5)', borderColor: '#e8365d', borderWidth: 1, borderRadius: 4 }]
  },
  options: chartDefaults
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
