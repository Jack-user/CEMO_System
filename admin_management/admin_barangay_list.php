<?php
// dashboard.php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

$page_title = "Bago City Barangay's";
include '../backend/admin_fetch_brgy.php';
?>

<body class="g-sidenav-show bg-gray-200">
  <?php include '../sidebar/admin_sidebar.php'; ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include '../includes/navbar.php'; ?>
    
    <?php if (isset($_SESSION['swal'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal']['type'] ?>',
            title: '<?= $_SESSION['swal']['message'] ?>',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['swal']); ?>
<?php endif; ?>


    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card shadow-lg mb-4">
            <div class="card-header p-0 position-relative mt-n2 mx-4 z-index-2">
              <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
                <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Health Risk Map</h5>
              </div>
            </div>
            <div class="card-body pt-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-danger">High</span>
                  <span class="badge bg-warning text-dark">Medium</span>
                  <span class="badge bg-success">Low</span>
                  <div class="ms-2 d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0">Barangay</label>
                    <select id="filterBarangay" class="form-select form-select-sm" style="min-width: 180px;"></select>
                  </div>
                  <div class="ms-2 d-flex align-items-center gap-2">
                    <!-- <label class="small text-muted mb-0">Risk</label>
                    <select id="filterRisk" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All</option>
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select> -->
                    <div class="form-check form-switch ms-2">
                      <!-- <input class="form-check-input" type="checkbox" role="switch" id="toggleOnlyHigh">
                      <label class="form-check-label small" for="toggleOnlyHigh">Only High</label> -->
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <label class="small text-muted mb-0">Lookback</label>
                  <select id="riskLookbackBrgyList" class="form-select form-select-sm" style="width: auto;">
                    <option value="7">7 days</option>
                    <option value="14">14 days</option>
                    <option value="30">30 days</option>
                  </select>
                  <!-- <button id="exportCsvBtn" class="btn btn-sm btn-outline-secondary">Export CSV</button> -->
                </div>
              </div>
              <div id="riskMapBrgyList" style="height: 420px; border-radius: 12px; overflow: hidden;"></div>
              <!-- Drilldown Modal -->
              <div class="modal fade" id="riskDrilldownModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h6 class="modal-title">Barangay Risk Details</h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="overflow:hidden;">
                      <div class="mb-2"><b id="drillBrgyName">—</b></div>
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <label class="small text-muted mb-0">Days</label>
                        <select id="drillDays" class="form-select form-select-sm" style="width:auto;">
                          <option value="7">7</option>
                          <option value="14">14</option>
                          <option value="30">30</option>
                        </select>
                      </div>
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="card p-3">
                            <h6 class="mb-2">Weekly (Mon–Sun)</h6>
                            <canvas id="drillWeeklyChart" height="160" style="width:100%;"></canvas>
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="card p-3">
                            <h6 class="mb-2">Monthly (Year-to-date)</h6>
                            <canvas id="drillMonthlyChart" height="160" style="width:100%;"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="mt-3 small text-muted">Note: sensor count × 0.001 = tons.</div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800"></h1>
          <div class="card shadow-lg">
            <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
              <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
                <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Barangay List</h5>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Barangay Name</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Facebook Link</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Location</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow">
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['barangay']); ?></h6>
                            </div>
                          </div>
                        </td>
                        <td class="align-middle text-sm">
                          <?php if (!empty($row['facebook_link']) && !empty($row['link_text'])): ?>
                            <a href="<?= htmlspecialchars($row['facebook_link']); ?>" target="_blank" class="text-primary text-xs font-weight-bold" style="text-decoration: none;">
                              <?= htmlspecialchars($row['link_text']); ?>
                            </a>
                          <?php else: ?>
                            <span class="text-xs text-secondary">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-sm">
                          <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                            <a href="#" class="badge badge-sm bg-gradient-secondary text-xs font-weight-bold view-location-btn"
                              data-bs-toggle="modal"
                              data-bs-target="#mapModal"
                              data-lat="<?= htmlspecialchars($row['latitude']); ?>"
                              data-lng="<?= htmlspecialchars($row['longitude']); ?>"
                              data-name="<?= htmlspecialchars($row['barangay']); ?>">
                              View Location
                            </a>
                          <?php else: ?>
                            <span class="text-xs text-secondary">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-center">
                          <button type="button" class="btn btn-link text-info px-2 py-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['brgy_id']; ?>">
                            <i class="material-symbols-rounded fs-5">edit</i>
                          </button>
                          <?php include '../modals/admin_edit_brgy_modal.php'; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include '../modals/admin_brgy_map_modal.php'; ?>
    <?php include '../includes/footer.php'; ?>
    
  </main>
</body>
</html>

    
  <script>

    // Using vector markers (no PNGs)

document.addEventListener('DOMContentLoaded', function () {
    let map;
    let barangayPolygons = {};
    let marker;
    let mapInitialized = false;

    const bagoBounds = L.latLngBounds(
        L.latLng(10.4300, 122.7800),
        L.latLng(10.6500, 123.1000)
    );

  function initializeMap(callback) {
        map = L.map('locationMap', {
            center: [10.5379, 122.8333],
            zoom: 13,
            maxBounds: bagoBounds,
            maxBoundsViscosity: 1.0,
            minZoom: 12,
            maxZoom: 18
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Load GeoJSON
        fetch('../barangay_api/brgy.geojson')
            .then(response => response.json())
            .then(data => {
                data.features.forEach(feature => {
                    const polygon = L.geoJSON(feature, {
                        style: {
                            color: '#0077B6',       
                            fillColor: '#0077B6',
                            fillOpacity: 0.05,
                            weight: 2
                        }
                    }).bindPopup(`<b>${feature.properties.name}</b> Geo-Fence`);

                    barangayPolygons[feature.properties.name] = polygon;
                });
                mapInitialized = true;

                if (callback) callback(); // Call back AFTER loading
            });
    }

    function showBarangayGeoFence(name) {
        // Remove all polygons
        for (const polygon of Object.values(barangayPolygons)) {
            map.removeLayer(polygon);
        }
        // Show the selected polygon
        if (barangayPolygons[name]) {
            barangayPolygons[name].addTo(map);
            barangayPolygons[name].setStyle({
                fillOpacity: 0.4,
                color: '#0077B6',
                weight: 2,
                opacity: 1
            });
        }
    }

    const mapModal = document.getElementById('mapModal');

    mapModal.addEventListener('shown.bs.modal', function () {
        let button = document.querySelector('.view-location-btn.active-trigger');

        if (!button) {
            button = document.querySelector('.view-location-btn[data-bs-target="#mapModal"]');
        }

        const lat = parseFloat(button.getAttribute('data-lat'));
        const lng = parseFloat(button.getAttribute('data-lng'));
        const name = button.getAttribute('data-name');

        function showOnMap() {
            map.setView([lat, lng], 14);

            showBarangayGeoFence(name);

            const markerStyle = { radius: 8, color: '#c62828', weight: 2, fillColor: '#ef5350', fillOpacity: 0.8 };
            if (!marker) {
              marker = L.circleMarker([lat, lng], markerStyle).addTo(map);
            } else {
              marker.setLatLng([lat, lng]);
              marker.setStyle(markerStyle);
            }

            marker.bindPopup(`<b>${name}</b>`).openPopup();

            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        }

        if (!mapInitialized) {
            initializeMap(showOnMap);
        } else {
            showOnMap();
        }
    });

    document.querySelectorAll('.view-location-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.view-location-btn').forEach(b => b.classList.remove('active-trigger'));
            this.classList.add('active-trigger');
        });
    });

    mapModal.addEventListener('hidden.bs.modal', function () {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        for (const polygon of Object.values(barangayPolygons)) {
            map.removeLayer(polygon);
        }
    });
});

// --- Health Risk Map for Barangay List page ---
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('riskMapBrgyList');
    if (!container) return;

    const bagoBounds = L.latLngBounds(
        L.latLng(10.4300, 122.7800),
        L.latLng(10.6500, 123.1000)
    );
    const map = L.map('riskMapBrgyList', {
        center: [10.5379, 122.8333],
        zoom: 13,
        maxBounds: bagoBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 12,
        maxZoom: 18
    });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const barangayPolygons = {};
    const markersLayer = L.layerGroup().addTo(map);
    let currentShownBrgy = null;
    let latestRiskData = [];
    let lastRiskSnapshot = new Map();

    function riskColor(r) {
        if (r === 'high') return '#e53935';
        if (r === 'medium') return '#fb8c00';
        return '#43A047';
    }

    function loadRiskData(showToastOnChange = false) {
        const lookback = document.getElementById('riskLookbackBrgyList').value || 7;
        fetch(`../api/get_health_risk_map.php?lookback_days=${encodeURIComponent(lookback)}`)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success || !Array.isArray(data.data)) return;
                // Clear markers and hide any shown polygons
                markersLayer.clearLayers();
                Object.values(barangayPolygons).forEach(p => { try { map.removeLayer(p); } catch (e) {} });
                latestRiskData = data.data;
                populateBarangayFilter(latestRiskData);
                const filtered = filterRiskData(latestRiskData);
                if (showToastOnChange) notifyRiskChanges(filtered);
                filtered.forEach(row => {
                    const name = row.barangay;
                    const risk = row.risk;
                    const tons = row.tons;
                    // Update polygon style for later display (do not show now)
                    if (barangayPolygons[name]) {
                        barangayPolygons[name].setStyle({ fillColor: riskColor(risk), fillOpacity: 0.30, color: riskColor(risk), weight: 2 });
                    }
                    // Add interactive marker
                    if (row.latitude && row.longitude) {
                        const icon = L.divIcon({
                            className: 'risk-pin',
                            html: `<div style="background:${riskColor(risk)};width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 8px rgba(0,0,0,.3)"></div>`,
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        });
                        const marker = L.marker([row.latitude, row.longitude], { icon }).addTo(markersLayer)
                            .bindPopup(`<b>${name}</b><br/>Risk: ${risk}<br/>Waste: ${tons.toFixed(2)} tons<br/><div class='mt-2 d-flex gap-2'><button class='btn btn-sm btn-outline-primary' data-action='route' data-name='${name}' data-lat='${row.latitude}' data-lng='${row.longitude}'>Route Assist</button><button class='btn btn-sm btn-outline-secondary' data-action='drill' data-name='${name}' data-brgy='${name}'>Drill-down</button></div>`);
                        marker.on('click', () => {
                            // Toggle: if same barangay is visible, hide it; otherwise show only this one
                            if (currentShownBrgy === name) {
                                if (barangayPolygons[name]) {
                                    try { map.removeLayer(barangayPolygons[name]); } catch (e) {}
                                }
                                currentShownBrgy = null;
                            } else {
                                Object.values(barangayPolygons).forEach(p => { try { map.removeLayer(p); } catch (e) {} });
                                if (barangayPolygons[name]) {
                                    barangayPolygons[name].addTo(map);
                                    currentShownBrgy = name;
                                    try { map.fitBounds(barangayPolygons[name].getBounds(), { maxZoom: 16, padding: [20, 20] }); } catch (e) {}
                                }
                            }
                        });
                        marker.on('popupopen', (ev) => {
                            const el = ev.popup.getElement();
                            if (!el) return;
                            const routeBtn = el.querySelector("[data-action='route']");
                            if (routeBtn) routeBtn.addEventListener('click', () => startRouteAssist(parseFloat(routeBtn.dataset.lat), parseFloat(routeBtn.dataset.lng)));
                            const drillBtn = el.querySelector("[data-action='drill']");
                            if (drillBtn) drillBtn.addEventListener('click', () => openDrilldown(name));
                        });
                    }
                });
            });
    }

    function populateBarangayFilter(data) {
        const sel = document.getElementById('filterBarangay');
        if (!sel) return;
        const current = sel.value;
        const brgys = Array.from(new Set(data.map(d => d.barangay))).sort();
        sel.innerHTML = '<option value="all">All Barangays</option>' + brgys.map(b => `<option value="${b}">${b}</option>`).join('');
        if (current) sel.value = current;
    }

    function filterRiskData(data) {
        const riskSel = document.getElementById('filterRisk');
        const onlyHigh = document.getElementById('toggleOnlyHigh');
        const brgySel = document.getElementById('filterBarangay');
        let out = data;
        if (brgySel && brgySel.value && brgySel.value !== 'all') out = out.filter(d => d.barangay === brgySel.value);
        if (onlyHigh && onlyHigh.checked) out = out.filter(d => d.risk === 'high');
        else if (riskSel && riskSel.value && riskSel.value !== 'all') out = out.filter(d => d.risk === riskSel.value);
        return out;
    }

    function notifyRiskChanges(list) {
        const current = new Map(list.map(d => [d.barangay, d.risk]));
        const changes = [];
        current.forEach((risk, name) => {
            const prev = lastRiskSnapshot.get(name);
            if (prev && prev !== risk) changes.push({ name, from: prev, to: risk });
        });
        lastRiskSnapshot = new Map(current);
        if (changes.length && typeof Swal !== 'undefined') {
            const html = changes.slice(0, 5).map(c => `<div><b>${c.name}</b>: ${c.from} → <span style='color:${riskColor(c.to)}'>${c.to}</span></div>`).join('');
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Risk updates', html, showConfirmButton: false, timer: 3500, timerProgressBar: true });
        }
    }

    // Export CSV for currently filtered list
    function exportCsv() {
        const filtered = filterRiskData(latestRiskData);
        const rows = [['Barangay','Risk','Tons','Latitude','Longitude']].concat(filtered.map(d => [d.barangay, d.risk, d.tons, d.latitude || '', d.longitude || '']));
        const csv = rows.map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'risk-visible.csv'; document.body.appendChild(a); a.click();
        setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 0);
    }

    // Route assist via Leaflet Routing Machine
    let routeControl = null;
    function startRouteAssist(lat, lng) {
        try { if (routeControl) map.removeControl(routeControl); } catch (e) {}
        if (!L.Routing || !L.Routing.control) { if (typeof Swal !== 'undefined') Swal.fire('Routing unavailable', 'Leaflet Routing Machine required.', 'warning'); return; }
        routeControl = L.Routing.control({
            waypoints: [ L.latLng(10.538274, 122.835230), L.latLng(lat, lng) ],
            routeWhileDragging: false, lineOptions: { styles: [{ color: '#2e7d32', weight: 5 }] }, createMarker: () => null,
            show: false, addWaypoints: false, draggableWaypoints: false
        }).addTo(map);
    }

    // Drilldown modal (weekly/monthly)
    let weeklyChart, monthlyChart;
    let currentDrillBrgyId = null;
    async function openDrilldown(barangayName) {
        const modalEl = document.getElementById('riskDrilldownModal');
        if (!modalEl) return;
        const brgyIdRaw = findBrgyIdByName(barangayName);
        if (!brgyIdRaw || brgyIdRaw <= 0) {
            if (typeof Swal !== 'undefined') Swal.fire('Missing barangay ID', 'Cannot load data for this barangay.', 'warning');
            return;
        }
        document.getElementById('drillBrgyName').textContent = barangayName;
        currentDrillBrgyId = brgyIdRaw;
        const brgyId = encodeURIComponent(brgyIdRaw);
        const bsModal = new bootstrap.Modal(modalEl);
        // Render charts when modal is shown to ensure proper canvas sizing
        const onShown = async () => {
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            try {
                const res = await fetch(`../api/get_brgy_monthly_waste.php?brgy_id=${brgyId}&year=${year}&month=${month}`);
                const d = await res.json();
                const ctxM = document.getElementById('drillMonthlyChart').getContext('2d');
                const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const dataVals = Array.isArray(d.monthlyTons) ? d.monthlyTons : [];
                if (monthlyChart) monthlyChart.destroy();
                monthlyChart = new Chart(ctxM, { type: 'line', data: { labels, datasets: [{ label: 'Tons', data: dataVals, borderColor: '#43A047', backgroundColor: 'rgba(67,160,71,0.2)', tension: 0.3, fill: true }] }, options: { animation: false, plugins: { legend: { display: false }, tooltip: { enabled: true, mode: 'index', intersect: false } }, responsive: false, maintainAspectRatio: false, events: ['mousemove','mouseout','click','touchstart','touchmove'], transitions: { active: { animation: { duration: 0 } } } } });
            } catch (err) {}
            await updateDailyChart();
        };
        const onHidden = () => {
            try { if (weeklyChart) weeklyChart.destroy(); } catch (e) {}
            try { if (monthlyChart) monthlyChart.destroy(); } catch (e) {}
            modalEl.removeEventListener('shown.bs.modal', onShown);
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
        };
        modalEl.addEventListener('shown.bs.modal', onShown);
        modalEl.addEventListener('hidden.bs.modal', onHidden);
        bsModal.show();
        // Bind change for days to refresh daily chart within the same barangay
        const daysSel = document.getElementById('drillDays');
        if (daysSel && !daysSel._bound) {
            daysSel.addEventListener('change', () => { updateDailyChart(); });
            daysSel._bound = true;
        }
    }

    async function updateDailyChart() {
        try {
            const brgyId = currentDrillBrgyId ? encodeURIComponent(currentDrillBrgyId) : null;
            if (!brgyId) return;
            const daysSel = document.getElementById('drillDays');
            const numDays = daysSel ? parseInt(daysSel.value, 10) || 7 : 7;
            const dailyRes = await fetch(`../api/get_brgy_daily_waste.php?brgy_id=${brgyId}&days=${numDays}`);
            const dailyJson = await dailyRes.json();
            const ctxW = document.getElementById('drillWeeklyChart').getContext('2d');
            const labels = Array.isArray(dailyJson.data) ? dailyJson.data.map(d => d.date.slice(5)) : [];
            const vals = Array.isArray(dailyJson.data) ? dailyJson.data.map(d => d.daily_tons) : [];
            if (weeklyChart) weeklyChart.destroy();
            weeklyChart = new Chart(ctxW, { type: 'bar', data: { labels, datasets: [{ label: 'Tons', data: vals, backgroundColor: '#43A047' }] }, options: { animation: false, plugins: { legend: { display: false }, tooltip: { enabled: true, mode: 'index', intersect: false } }, responsive: false, maintainAspectRatio: false, events: ['mousemove','mouseout','click','touchstart','touchmove'], transitions: { active: { animation: { duration: 0 } } } } });
        } catch (e) {}
    }

    function findBrgyIdByName(name) {
        // Attempt to match from latestRiskData if id present in future; fallback to 0
        const m = latestRiskData.find(d => d.barangay === name && d.brgy_id);
        return m ? m.brgy_id : 0;
    }

    // Load polygons once (hidden by default), then risk data
    fetch('../barangay_api/brgy.geojson')
        .then(r => r.json())
        .then(geojson => {
            geojson.features.forEach(f => {
                const name = f.properties && f.properties.name ? f.properties.name : undefined;
                if (!name) return;
                const poly = L.geoJSON(f, { style: { color: '#0077B6', fillOpacity: 0.15, weight: 2 } });
                barangayPolygons[name] = poly;
                // Do not add to map now; will be shown on marker click
            });
            loadRiskData();
        });

    // Click on empty map area clears any visible polygon
    map.on('click', (e) => {
        if (currentShownBrgy && barangayPolygons[currentShownBrgy]) {
            try { map.removeLayer(barangayPolygons[currentShownBrgy]); } catch (err) {}
            currentShownBrgy = null;
        }
    });

    const sel = document.getElementById('riskLookbackBrgyList');
    if (sel) sel.addEventListener('change', () => loadRiskData(false));
    const riskSel = document.getElementById('filterRisk');
    if (riskSel) riskSel.addEventListener('change', () => loadRiskData(false));
    const onlyHigh = document.getElementById('toggleOnlyHigh');
    if (onlyHigh) onlyHigh.addEventListener('change', () => loadRiskData(false));
    const brgySel = document.getElementById('filterBarangay');
    if (brgySel) brgySel.addEventListener('change', () => loadRiskData(false));
    const exportBtn = document.getElementById('exportCsvBtn');
    if (exportBtn) exportBtn.addEventListener('click', exportCsv);

    // Auto refresh every 45s with change toast
    setInterval(() => loadRiskData(true), 45000);
});
</script>
      <style>
/* Ensure navbar z-index is proper */
        .navbar-main {
            z-index: 1030;
            backdrop-filter: saturate(200%) blur(30px);
            background-color: rgba(255, 255, 255, 0.8) !important;
        }
        
        /* Fix dropdown positioning */
        .dropdown-menu {
            z-index: 1040;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin: 0 0.5rem;
            transform: translateX(5px);
        }
        
        /* Toast positioning */
        .toast {
            min-width: 350px;
        }
        
        /* Mobile sidebar toggle styling */
        .sidenav-toggler-inner {
            cursor: pointer;
        }
        
        /* Ensure Font Awesome icons are visible */
        .fa-solid, .fa-regular {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }
        
        .fa-regular {
            font-weight: 400 !important;
        }
        
        /* Fix badge positioning */
        .nav-item .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            z-index: 1;
        }
        
        /* Breadcrumb styling */
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            color: #adb5bd;
        }
        
        /* User info styling */
        .nav-link.dropdown-toggle::after {
            display: none;
        }
        
        /* Success indicator dot */
        .bg-success {
            background-color: #28a745 !important;
        }
      </style>
</body>
</html>



