<?php
$title = 'Mapa de Ocorrências';
require_once __DIR__ . '/includes/header.php';

$markers = [];
$res = db()->query(
    "SELECT o.id, o.title, o.risk_level, o.latitude, o.longitude, v.full_name victim_name
     FROM occurrences o JOIN victims v ON v.id=o.victim_id
     WHERE o.latitude IS NOT NULL AND o.longitude IS NOT NULL"
);
while ($row = $res->fetch_assoc()) $markers[] = $row;

$panic_markers = [];
$res2 = db()->query(
    "SELECT p.id, p.location, p.latitude, p.longitude, v.full_name victim_name
     FROM panic_alerts p JOIN victims v ON v.id=p.victim_id
     WHERE p.resolved=0 AND p.latitude IS NOT NULL AND p.longitude IS NOT NULL"
);
while ($row = $res2->fetch_assoc()) $panic_markers[] = $row;
?>

<p style="color:var(--muted);font-size:13px;margin-bottom:16px">
  Exibindo <?= count($markers) ?> ocorrência(s) e <?= count($panic_markers) ?> alerta(s) de pânico ativos com coordenadas registradas.
</p>

<div class="card" style="padding:0;overflow:hidden">
  <div id="map" style="height:520px;width:100%"></div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([-22.4339, -46.9533], 13);
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; CartoDB', maxZoom: 19
}).addTo(map);

const riskColors = { A: '#e8365d', B: '#f59e0b', C: '#10b981' };

const markers = <?= json_encode($markers) ?>;
markers.forEach(m => {
  const color = riskColors[m.risk_level] || '#64748b';
  const icon = L.divIcon({
    html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 8px ${color}66"></div>`,
    className: '', iconSize: [14,14], iconAnchor: [7,7]
  });
  L.marker([m.latitude, m.longitude], {icon})
    .addTo(map)
    .bindPopup(`<b>${m.title}</b><br>Vítima: ${m.victim_name}<br>Risco: ${m.risk_level}`);
});

const panics = <?= json_encode($panic_markers) ?>;
panics.forEach(p => {
  const icon = L.divIcon({
    html: `<div style="width:18px;height:18px;border-radius:50%;background:#e8365d;border:3px solid #fff;animation:pulse 1s infinite;box-shadow:0 0 12px #e8365d"></div>`,
    className: '', iconSize: [18,18], iconAnchor: [9,9]
  });
  L.marker([p.latitude, p.longitude], {icon})
    .addTo(map)
    .bindPopup(`<b>🚨 ALERTA DE PÂNICO</b><br>Vítima: ${p.victim_name}<br>${p.location || ''}`);
});

// Legenda
const legend = L.control({position:'bottomright'});
legend.onAdd = () => {
  const d = L.DomUtil.create('div','');
  d.style.cssText = 'background:#161b24;padding:12px 16px;border-radius:8px;border:1px solid #1e2535;color:#e2e8f0;font-size:12px;font-family:Sora,sans-serif';
  d.innerHTML = '<b>Legenda</b><br><br>' +
    '<span style="color:#e8365d">● </span>Risco A — Alto<br>' +
    '<span style="color:#f59e0b">● </span>Risco B — Médio<br>' +
    '<span style="color:#10b981">● </span>Risco C — Baixo<br>' +
    '<span style="color:#e8365d">● </span>Pânico ativo';
  return d;
};
legend.addTo(map);

</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
