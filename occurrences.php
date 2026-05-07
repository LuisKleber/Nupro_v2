<?php
$title = 'Ocorrências';
require_once __DIR__ . '/includes/header.php';
require_roles(['admin','coordenacao','pm','gm']);

// POST — criar ocorrência
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $vid   = (int)$_POST['victim_id'];
    $ttl   = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $risk  = $_POST['risk_level'] ?: 'B';
    $reg   = trim($_POST['region'] ?? '');
    $idate = $_POST['incident_date'] ?: null;
    $lat   = (isset($_POST['latitude'])  && $_POST['latitude']  !== '') ? (float)$_POST['latitude']  : null;
    $lng   = (isset($_POST['longitude']) && $_POST['longitude'] !== '') ? (float)$_POST['longitude'] : null;
    $uid   = current_user()['id'];

    if (!$vid || !$ttl) {
        flash('error', 'Vítima e título são obrigatórios.');
        header('Location: /occurrences.php?action=new'); exit;
    }

    $stmt = db()->prepare(
        'INSERT INTO occurrences (victim_id,title,description,risk_level,region,incident_date,latitude,longitude,created_by)
         VALUES (?,?,?,?,?,?,?,?,?)'
    );
    $stmt->bind_param('isssssddi', $vid,$ttl,$desc,$risk,$reg,$idate,$lat,$lng,$uid);
    $stmt->execute();
    audit_log('CREATE','occurrences',"Ocorrência: $ttl");
    flash('success',"Ocorrência \"$ttl\" registrada com sucesso.");
    header('Location: /occurrences.php'); exit;
}

// Vítimas para select
$victims_list = db()->query('SELECT id, full_name FROM victims ORDER BY full_name');

// Listar ocorrências
$list = db()->query(
    'SELECT o.*, v.full_name victim_name, u.name agent_name
     FROM occurrences o JOIN victims v ON v.id=o.victim_id LEFT JOIN users u ON u.id=o.created_by
     ORDER BY o.created_at DESC'
);
$show_form = ($_GET['action'] ?? '') === 'new';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <h3 style="font-size:15px;color:var(--muted)">Registro de ocorrências — Mogi Mirim / SP</h3>
  <a href="/occurrences.php?action=new" class="btn btn-primary">+ Nova ocorrência</a>
</div>

<?php if ($show_form): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Registrar ocorrência</div>
  <form method="POST" action="/occurrences.php">
    <input type="hidden" name="_action" value="create">
    <!-- Campos hidden que serão preenchidos pelo mapa -->
    <input type="hidden" name="latitude"  id="field_lat">
    <input type="hidden" name="longitude" id="field_lng">

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
        <label>Nível de risco *</label>
        <select class="form-control" name="risk_level" required>
          <option value="A">A — Alto</option>
          <option value="B" selected>B — Médio</option>
          <option value="C">C — Baixo</option>
        </select>
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Título *</label>
        <input class="form-control" name="title" placeholder="Descreva brevemente a ocorrência..." required>
      </div>
      <div class="form-group">
        <label>Bairro / Região</label>
        <input class="form-control" name="region" id="field_region" placeholder="Ex: Centro, Jd. América...">
      </div>
      <div class="form-group">
        <label>Data do fato</label>
        <input class="form-control" type="date" name="incident_date" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Descrição</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Detalhes da ocorrência..."></textarea>
      </div>
    </div>

    <!-- MAPA INTERATIVO -->
    <div class="form-group" style="grid-column:span 2">
      <label>📍 Localização no Mapa (Mogi Mirim - SP)</label>

      <div class="map-search-bar">
        <input class="form-control" id="addr_search" placeholder="Buscar endereço em Mogi Mirim... Ex: Rua XV de Novembro, 100" style="font-size:13px">
        <button type="button" class="btn btn-outline btn-sm" onclick="searchAddress()">🔍 Buscar</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearPin()">✕ Limpar</button>
      </div>

      <div style="font-size:12px;color:var(--muted);margin-bottom:8px">
        💡 Busque o endereço ou clique diretamente no mapa para marcar a localização exata.
      </div>

      <div id="oc_map" style="height:380px;border-radius:var(--radius);border:1px solid var(--border);overflow:hidden"></div>

      <div class="map-coords-info" id="coords_display">
        📍 Coordenadas selecionadas: <span id="coords_text"></span>
        &nbsp;·&nbsp; <span id="addr_result" style="color:var(--muted)"></span>
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:8px">
      <button type="submit" class="btn btn-primary">✓ Salvar ocorrência</button>
      <a href="/occurrences.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>

<!-- Leaflet apenas para o formulário -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const MOGI = [-22.4339, -46.9533];
const map  = L.map('oc_map').setView(MOGI, 13);

L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; CartoDB', maxZoom: 19
}).addTo(map);

// Marcador de referência (centro de Mogi Mirim)
L.circle(MOGI, {radius:8000, color:'#3b82f6', fillColor:'#3b82f6', fillOpacity:0.04, weight:1}).addTo(map);

let pin = null;

function placePin(lat, lng, addrLabel) {
  if (pin) map.removeLayer(pin);
  const icon = L.divIcon({
    html: `<div style="width:18px;height:18px;border-radius:50%;background:#e8365d;border:3px solid #fff;box-shadow:0 0 12px rgba(232,54,93,.7)"></div>`,
    className: '', iconSize: [18,18], iconAnchor: [9,9]
  });
  pin = L.marker([lat, lng], {icon}).addTo(map);
  document.getElementById('field_lat').value = lat.toFixed(7);
  document.getElementById('field_lng').value = lng.toFixed(7);
  const display = document.getElementById('coords_display');
  display.style.display = 'block';
  document.getElementById('coords_text').textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
  document.getElementById('addr_result').textContent  = addrLabel || '';
}

function clearPin() {
  if (pin) { map.removeLayer(pin); pin = null; }
  document.getElementById('field_lat').value = '';
  document.getElementById('field_lng').value = '';
  document.getElementById('coords_display').style.display = 'none';
  document.getElementById('addr_search').value = '';
}

map.on('click', function(e) {
  placePin(e.latlng.lat, e.latlng.lng, 'marcado manualmente');
  // Tentar geocodificação reversa
  fetch(`https://nominatim.openstreetmap.org/reverse?lat=${e.latlng.lat}&lon=${e.latlng.lng}&format=json&accept-language=pt`)
    .then(r => r.json())
    .then(d => {
      const addr = d.display_name || '';
      document.getElementById('addr_result').textContent = addr.split(',').slice(0,3).join(',');
      // preencher região automaticamente se estiver vazio
      const regionField = document.getElementById('field_region');
      if (!regionField.value && d.address) {
        regionField.value = d.address.suburb || d.address.neighbourhood || d.address.city_district || '';
      }
    }).catch(() => {});
});

async function searchAddress() {
  const q = document.getElementById('addr_search').value.trim();
  if (!q) return;
  const query = encodeURIComponent(q + ', Mogi Mirim, SP, Brasil');
  try {
    const r    = await fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=5&accept-language=pt`);
    const data = await r.json();
    if (!data.length) {
      alert('Endereço não encontrado. Tente outro nome ou clique diretamente no mapa.');
      return;
    }
    const best = data[0];
    map.setView([+best.lat, +best.lon], 17);
    placePin(+best.lat, +best.lon, best.display_name.split(',').slice(0,3).join(','));
    // preencher bairro automaticamente
    if (best.address) {
      const regionField = document.getElementById('field_region');
      if (!regionField.value) {
        regionField.value = best.address?.suburb || best.address?.neighbourhood || best.address?.city_district || '';
      }
    }
  } catch(err) {
    alert('Erro ao buscar endereço. Verifique sua conexão ou clique no mapa manualmente.');
  }
}

document.getElementById('addr_search').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') { e.preventDefault(); searchAddress(); }
});
</script>

<?php endif; ?>

<div class="card">
  <div class="card-title">Ocorrências registradas</div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Título</th><th>Vítima</th><th>Risco</th><th>Região</th><th>Data fato</th><th>Localização</th><th>Agente</th></tr>
      </thead>
      <tbody>
      <?php while ($row = $list->fetch_assoc()): ?>
        <tr>
          <td style="font-family:monospace;color:var(--muted)">#<?= e($row['id']) ?></td>
          <td><?= e($row['title']) ?></td>
          <td><?= e($row['victim_name']) ?></td>
          <td><span class="badge-risk <?= e($row['risk_level']) ?>"><?= risk_label($row['risk_level']) ?></span></td>
          <td><?= e($row['region'] ?: '—') ?></td>
          <td style="font-size:12px;color:var(--muted)"><?= $row['incident_date'] ? date('d/m/Y', strtotime($row['incident_date'])) : '—' ?></td>
          <td style="font-size:11px;color:var(--muted);font-family:monospace">
            <?php if ($row['latitude']): ?>
              <a href="/map.php" style="color:var(--blue);text-decoration:none" title="Ver no mapa">
                📍 <?= number_format($row['latitude'],4) ?>, <?= number_format($row['longitude'],4) ?>
              </a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td><?= e($row['agent_name'] ?? '—') ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
