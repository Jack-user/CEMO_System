<?php
session_start();
include '../includes/conn.php';

    // Check if the user is logged in client
    if (!isset($_SESSION['client_id'])) {
        // Redirect to the login page if not logged in
        header("Location: ../login_page/sign-in.php");
        exit();
    }
        // Fetch all barangays from the database
        $sql = "SELECT barangay, latitude, longitude FROM barangays_table WHERE city = 'Bago City'";
        $result = $conn->query($sql);
        $allBarangays = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $allBarangays[] = $row;
            }
        } else {
            echo "<p class='text-center text-secondary'>No barangays found.</p>";
        }

        
        // Fetch all routes from the route table
        $sql = "SELECT r.*, d.first_name, d.last_name, w.vehicle_name, w.plate_no, w.vehicle_capacity
                FROM route_table r
                LEFT JOIN driver_table d ON r.driver_id = d.driver_id
                LEFT JOIN waste_service_table w ON r.route_id = w.route_id";

        $result = $conn->query($sql);

                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):

            // Get client information
            $client_id = $_SESSION['client_id'];
            $stmt = $pdo->prepare("SELECT barangay FROM client_table WHERE client_id = ?");
            $stmt->execute([$client_id]);
            $client = $stmt->fetch();
            if (!$client) {
                die("Client not found.");
            }
            $clientBarangay = $client['barangay'];


            $page_title = "Bago City Map";
            include '../includes/header.php'; // Includes the head section and styles
?>


<body class="g-sidenav-show bg-gray-200">
    <!-- Sidebar -->
    <?php include '../sidebar/client_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <?php if (isset($_SESSION['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
    <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>




        <!-- Page Content -->
        <div class="container mt-4">
            <h2 class="text-center">Bago City Map</h2>
            
            <!-- Map + Vehicle Panel Container -->
            <div class="d-flex flex-wrap gap-3 align-items-stretch">
                <div class="map-box" style="flex: 1 1 60%; height: 400px; resize: both; min-width: 320px;">
                    <div id="map" style="width: 100%; height: 100%; border-radius: 8px; border: 1px solid #ccc;"></div>
                    <!-- Floating Trail Controls -->
                    <div class="floating-trail-controls">
                        <button type="button" class="btn btn-primary btn-floating" id="viewTrail" title="View Trail">
                            <i class="fas fa-route"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-floating" id="followGps" title="Follow GPS">
                            <i class="fas fa-location-arrow"></i>
                        </button>
                    </div>
                </div>

                <!-- Vehicle Info Panel -->
                <div class="card vehicle-panel" style="flex: 1 1 38%; min-width: 300px;">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Vehicle Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="text-sm text-secondary">Vehicle</div>
                                <div id="vehicleName" class="text-dark fw-bold">—</div>
                                <div class="text-xs text-secondary">Driver: <span id="driverName">—</span></div>
                            </div>
                            <span id="vehicleStatus" class="badge bg-secondary">—</span>
                        </div>

                        <div class="mb-3">
                            <div class="text-sm text-secondary mb-1">Current Location</div>
                            <div id="vehicleLocation" class="text-dark">—</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-sm text-secondary mb-1">Route</div>
                            <div id="vehicleRoute" class="text-dark">—</div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-sm text-secondary">Capacity</div>
                                <div class="text-sm"><span id="vehicleCapacityPercent">0</span>%</div>
                            </div>
                            <div class="vehicle-figure position-relative">
                                <div class="water-fill" id="waterFill"></div>
                                <div class="vehicle-icon"><i class="fas fa-truck" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hidden span to pass client barangay -->
            <span id="clientBarangay" style="display: none;"><?= htmlspecialchars($clientBarangay) ?></span>

            <!-- Add spacing between map and table -->
            <div class="mt-4">
                <h4 class="text-center mb-3">Vehicle Routes</h4>
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center text-uppercase text-xs font-weight-bolder">Vehicle List</th>
                            <th class="text-center text-uppercase text-xs font-weight-bolder">Route</th>
                            <th class="text-center text-uppercase text-xs font-weight-bolder">Actions</th>
                            <th class="text-center text-uppercase text-xs font-weight-bolder">Tools</th>
                        </tr>
                    </thead>
                    <tbody>
            <tr>
                <td>
                    <div class="d-flex px-2 py-1">
                        <div>
                            <img src="../assets/img/logo.png" class="avatar avatar-sm me-3 border-radius-lg" alt="route">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['vehicle_name']); ?></h6>
                        </div>
                    </div>
                </td>
                <td>
                    <p class="text-xs font-weight-bold mb-0 text-center">
                        Start Point: <?= htmlspecialchars($row['start_point']); ?> → End Point: <?= htmlspecialchars($row['end_point']); ?>
                    </p>
                </td>
                <td class="align-middle">
                    <div class="d-flex align-items-center justify-content-center">
                        <a href="#"
                        class="badge badge-sm bg-gradient-success view-route"
                        data-bs-toggle="tooltip"
                        data-bs-original-title="View Route Details"
                        data-barangay="<?= htmlspecialchars($row['end_point']); ?>">
                            View
                            <span class="material-symbols-rounded opacity-10" style="font-size: 0.9rem;">eye_tracking</span>
                        </a>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center justify-content-center">
                        <a href="#"
                        class="badge badge-sm bg-gradient-info details-route-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#detailsRouteModal"
                        data-vehicle="<?= htmlspecialchars($row['vehicle_name']); ?>"
                        data-plate="<?= htmlspecialchars($row['plate_no']); ?>"
                        data-type="<?= htmlspecialchars($row['vehicle_capacity']); ?>"
                        data-driver="<?= htmlspecialchars($row['first_name']); ?>"
                        data-start="<?= htmlspecialchars($row['start_point']); ?>"
                        data-end="<?= htmlspecialchars($row['end_point']); ?>">
                        Details <span class="material-symbols-rounded opacity-10" style="font-size: 0.9rem;">info</span>
                        </a>
                    </div>
                </td>
            </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="4" class="text-center text-secondary">No routes found.</td>
            </tr>
        <?php endif; ?>

                <!-- Details Route Modal -->
                <div class="modal fade" id="detailsRouteModal" tabindex="-1" aria-labelledby="detailsRouteLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title" id="detailsRouteLabel">Vehicle & Route Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Vehicle Name:</strong> <span id="detailsVehicleName"></span></li>
                                    <li class="list-group-item"><strong>Driver Name:</strong> <span id="detailsDriverName"></span></li>
                                    <li class="list-group-item"><strong>Plate Number:</strong> <span id="detailsPlateNumber"></span></li>
                                    <li class="list-group-item"><strong>Vehicle Capacity:</strong> <span id="detailsVehicleType"></span></li>
                                    <li class="list-group-item"><strong>Start Point:</strong> <span id="detailsStartPoint"></span></li>
                                    <li class="list-group-item"><strong>End Point:</strong> <span id="detailsEndPoint"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    </div>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <?php include '../includes/footer.php'; ?>
    </main>

    <!-- Core JS Files -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    

    <!-- Tooltip and Scrollbar Init -->
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    <!-- Leaflet Map JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Leaflet Routing Machine CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

    <!-- Material Dashboard JS -->
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

    <style>
    .map-box {
        overflow: hidden;
        position: relative;
    }
    .floating-trail-controls {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    }   
    .map-box::after {
        content: '';
        position: absolute;
        right: 0;
        bottom: 0;
        width: 5px;
        height: 5px;
        background: #000;
        cursor: nwse-resize;
        z-index: 999;
    }
    .floating-trail-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
    .btn-floating {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .btn-floating:hover {
        transform: translateY(-2px);
    }
    /* Vehicle panel water fill */
    .vehicle-panel .vehicle-figure {
        height: 160px;
        border-radius: 12px;
        background: #f8fafc;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .vehicle-panel .water-fill {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 0%;
        background: linear-gradient(180deg, #4fc3f7 0%, #0288d1 100%);
        transition: height 0.6s ease;
    }
    .vehicle-panel .vehicle-icon {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d47a1;
        font-size: 64px;
        opacity: 0.9;
        pointer-events: none;
    }
</style>
</body>
</html>
<!-- Hidden span to pass client barangay -->
<script>
        // Global variables
        var map;
        var allBarangays = [];
        var barangayPolygons = {};
        var geojsonLoaded = false;
        var gpsMarker;
        var gpsTrail = [];
        var trailPolyline = null;
        var trailVisible = false;
        var followingGps = false;

        // Initialize map
        window.onload = function () {
            var bagoBounds = L.latLngBounds(
                L.latLng(10.4300, 122.7800),
                L.latLng(10.6500, 123.1000)
            );

            map = L.map('map', {
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

            // Add Bago City Hall marker
            L.marker([10.538274, 122.835230]).addTo(map)
                .bindPopup("<b>Bago City Hall</b>")
                .openPopup();

            // Fetch barangays and add markers
            fetch("../barangay_api/get_barangays.php")
                .then(response => response.json())
                .then(data => {
                    allBarangays = data;
                    data.forEach(barangay => {
                        if (barangay.latitude && barangay.longitude && barangay.city === 'Bago City') {
                            L.marker([parseFloat(barangay.latitude), parseFloat(barangay.longitude)])
                                .addTo(map)
                                .bindPopup(`<b>${barangay.barangay}</b><br>Bago City`);
                        }
                    });
                })
                .catch(error => console.error("Error fetching barangays:", error));

            // Load GeoJSON for polygons (used for entry detection only)
            fetch("../barangay_api/brgy.geojson")
                .then(response => response.json())
                .then(geojson => {
                    geojson.features.forEach(feature => {
                        var name = feature.properties.name;
                        var polygon = L.geoJSON(feature);
                        barangayPolygons[name] = polygon;
                    });
                    geojsonLoaded = true;
                })
                .catch(error => console.error("Error loading GeoJSON:", error));

            // Start GPS update
            updateGpsMarker();
            setInterval(updateGpsMarker, 1000);

            // Initialize controls
            initializeTrailControls();
            initializeFollowGps();
            initVehiclePanel();
        };

        // Update GPS marker
        function updateGpsMarker() {
            fetch('../admin_management/get_latest_gps.php')
                .then(res => res.json())
                .then(data => {
                    // Remove previous GPS marker
                    if (gpsMarker && map.hasLayer(gpsMarker)) {
                        map.removeLayer(gpsMarker);
                    }

                    if (!data.gps_points || !Array.isArray(data.gps_points) || data.gps_points.length === 0) return;

                    var point = data.gps_points[0];
                    if (!point.latitude || !point.longitude) return;

                    const latLng = [point.latitude, point.longitude];

                    // Add GPS marker
                    let icon = L.icon({
                        iconUrl: '../assets/img/gps_icon.png',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        popupAnchor: [0, -30]
                    });
                    gpsMarker = L.marker(latLng, { icon: icon })
                        .addTo(map)
                        .bindPopup("🚗 Current Vehicle Location");

                    // Add to trail
                    gpsTrail.push(latLng);
                    if (gpsTrail.length > 100) gpsTrail.shift();

                    // Update trail line if visible
                    if (trailVisible) {
                        updateTrailLine();
                    }

                    // Center map on GPS if following
                    if (followingGps) {
                        map.setView(latLng, map.getZoom());
                    }

                    // Track barangay entry/exit
                    trackBarangayEntry(latLng);

                    // Update ETA if vehicle is in client's barangay
                    const clientBarangay = document.getElementById('clientBarangay').textContent.trim();
                    if (window.lastBrgy === clientBarangay) {
                        updateETA();
                    }
                })
                .catch(err => console.error("Error fetching GPS:", err));
        }

        // Track if vehicle enters/exits a barangay (only for client's barangay)
        function trackBarangayEntry(latLng) {
            let insideAny = false;
            let currentBrgy = null;

            Object.keys(barangayPolygons).forEach(name => {
                var polygon = barangayPolygons[name];
                if (polygon && geojsonLoaded) {
                    polygon.eachLayer(function(layer) {
                        if (layer instanceof L.Polygon) {
                            if (layer.contains && layer.contains(latLng)) {
                                insideAny = true;
                                currentBrgy = name;
                            } else {
                                // Fallback point-in-polygon check
                                var polyLatLngs = layer.getLatLngs()[0];
                                var x = latLng[1], y = latLng[0];
                                var inside = false;
                                for (var i = 0, j = polyLatLngs.length - 1; i < polyLatLngs.length; j = i++) {
                                    var xi = polyLatLngs[i].lng, yi = polyLatLngs[i].lat;
                                    var xj = polyLatLngs[j].lng, yj = polyLatLngs[j].lat;
                                    var intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi + 1e-10) + xi);
                                    if (intersect) inside = !inside;
                                }
                                if (inside) {
                                    insideAny = true;
                                    currentBrgy = name;
                                }
                            }
                        }
                    });
                }
            });

            const clientBarangay = document.getElementById('clientBarangay').textContent.trim();

            // Only trigger for client's barangay
            if (insideAny && currentBrgy === clientBarangay) {
                if (window.lastBrgy !== currentBrgy) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Vehicle Arrived!',
                        text: `The vehicle has arrived at your barangay: ${currentBrgy}.`,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK'
                    });
                    window.lastBrgy = currentBrgy;
                }
            } else if (window.lastBrgy === clientBarangay && (!insideAny || currentBrgy !== clientBarangay)) {
                window.lastBrgy = null;
            }
        }

        // Vehicle info panel
        function initVehiclePanel() {
            if (window.vehicleInfoInterval) return;
            loadVehicleInfo();
            window.vehicleInfoInterval = setInterval(loadVehicleInfo, 5000);
        }

        function loadVehicleInfo() {
            fetch('../api/get_vehicle_info.php')
                .then(r => r.json())
                .then(d => {
                    if (!d || !d.success) return;
                    const nameEl = document.getElementById('vehicleName');
                    const driverEl = document.getElementById('driverName');
                    const statusEl = document.getElementById('vehicleStatus');
                    const locEl = document.getElementById('vehicleLocation');
                    const routeEl = document.getElementById('vehicleRoute');
                    const capEl = document.getElementById('vehicleCapacityPercent');
                    const waterEl = document.getElementById('waterFill');

                    if (nameEl) nameEl.textContent = d.vehicle_name || 'Vehicle';
                    if (driverEl) driverEl.textContent = d.driver_name || 'N/A';
                    if (locEl) locEl.textContent = d.current_location || 'Unknown';
                    if (routeEl) routeEl.textContent = (d.start_point ? d.start_point : '—') + (d.end_point ? ' → ' + d.end_point : '');
                    if (capEl) capEl.textContent = parseInt(d.capacity_percent || 0, 10);
                    if (waterEl) waterEl.style.height = (d.capacity_percent || 0) + '%';

                    if (statusEl) {
                        statusEl.textContent = d.status || 'On going';
                        statusEl.classList.remove('bg-secondary', 'bg-warning', 'bg-success');
                        const s = (d.status || '').toLowerCase();
                        if (s === 'collecting') statusEl.classList.add('bg-warning');
                        else if (s === 'collected') statusEl.classList.add('bg-success');
                        else statusEl.classList.add('bg-secondary');
                    }
                })
                .catch(() => {});
        }

        // Update trail line
        function updateTrailLine() {
            if (trailPolyline) {
                map.removeLayer(trailPolyline);
            }
            if (gpsTrail.length > 1) {
                trailPolyline = L.polyline(gpsTrail, {
                    color: '#ff6b35',
                    weight: 3,
                    opacity: 0.8
                }).addTo(map);
            }
        }

        // Initialize View/Hide Trail button
        function initializeTrailControls() {
            const trailBtn = document.getElementById('viewTrail');
            if (!trailBtn) return;

            trailBtn.addEventListener('click', function () {
                trailVisible = !trailVisible;

                if (trailVisible) {
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    this.title = 'Hide Trail';
                    if (gpsTrail.length > 0) updateTrailLine();
                    Swal.fire({
                        icon: 'success',
                        title: 'Trail Visible',
                        text: 'GPS trail is now visible.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                } else {
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-primary');
                    this.innerHTML = '<i class="fas fa-route"></i>';
                    this.title = 'View Trail';
                    if (trailPolyline) {
                        map.removeLayer(trailPolyline);
                        trailPolyline = null;
                    }
                    Swal.fire({
                        icon: 'info',
                        title: 'Trail Hidden',
                        text: 'GPS trail is now hidden.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Initialize Follow GPS button
        function initializeFollowGps() {
            const followBtn = document.getElementById('followGps');
            if (!followBtn) return;

            followBtn.addEventListener('click', function () {
                followingGps = !followingGps;

                if (followingGps) {
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-info');
                    this.innerHTML = '<i class="fas fa-bullseye"></i>';
                    this.title = 'Stop Following';
                    if (gpsMarker) {
                        map.setView(gpsMarker.getLatLng(), map.getZoom());
                    }
                    Swal.fire({
                        icon: 'info',
                        title: 'Following GPS',
                        text: 'Map is now following the vehicle.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                } else {
                    this.classList.remove('btn-info');
                    this.classList.add('btn-outline-primary');
                    this.innerHTML = '<i class="fas fa-location-arrow"></i>';
                    this.title = 'Follow GPS';
                    Swal.fire({
                        icon: 'info',
                        title: 'Stopped Following',
                        text: 'Map will no longer follow the vehicle.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Estimate arrival time to client's barangay
        function updateETA() {
            if (!alertsEnabled || etaAlertShown) return; // Skip if disabled or already shown

            const clientBarangay = document.getElementById('clientBarangay').textContent.trim();
            const barangay = allBarangays.find(b => b.barangay === clientBarangay);
            if (!barangay || !gpsMarker) return;

            const dest = L.latLng(parseFloat(barangay.latitude), parseFloat(barangay.longitude));
            const current = gpsMarker.getLatLng();
            const distance = current.distanceTo(dest) / 1000; // km
            const speed = 30; // km/h
            const time = (distance / speed) * 60; // minutes

            let msg;
            if (time < 60) {
                msg = `Arriving in ${Math.round(time)} minute${Math.round(time) !== 1 ? 's' : ''}.`;
            } else {
                const hrs = Math.floor(time / 60);
                const mins = Math.round(time % 60);
                msg = `Arriving in ${hrs}h ${mins}m.`;
            }

            Swal.fire({
                icon: 'info',
                title: 'Estimated Arrival',
                text: msg,
                timer: 5000,
                showConfirmButton: false
            });

            etaAlertShown = true; // Mark as shown
        }

        // View Route - Only show route line, no polygon
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.view-route');
            buttons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const barangayName = button.getAttribute('data-barangay');
                    const barangay = allBarangays.find(b => b.barangay === barangayName);
                    if (!barangay || !barangay.latitude || !barangay.longitude) {
                        Swal.fire('Error', `Coordinates not found for ${barangayName}`, 'error');
                        return;
                    }

                    const startPoint = [10.538274, 122.835230];
                    const endPoint = [parseFloat(barangay.latitude), parseFloat(barangay.longitude)];

                    // Remove any existing routing
                    map.eachLayer(layer => {
                        if (layer instanceof L.Routing.Control) {
                            map.removeControl(layer);
                        }
                    });

                    // Add routing (only the line)
                    L.Routing.control({
                        waypoints: [
                            L.latLng(startPoint[0], startPoint[1]),
                            L.latLng(endPoint[0], endPoint[1])
                        ],
                        routeWhileDragging: false,
                        lineOptions: { styles: [{ color: 'blue', weight: 4 }] },
                        createMarker: function () { return null; },
                        show: false,
                        addWaypoints: false,
                        draggableWaypoints: false
                    }).addTo(map);
                });
            });
        });
    </script>


