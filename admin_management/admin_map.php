<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}
$page_title = "Bago City Map";
include '../includes/header.php';
?>

        <?php if (isset($_SESSION['msg'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '<?= $_SESSION['msg']; ?>',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

<body class="g-sidenav-show bg-gray-100">
    <?php include '../sidebar/admin_sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>
        <?php include '../includes/conn.php'; ?>
        <div class="container mt-3">
            <h2 class="text-center"> City Map</h2>
            <div class="d-flex flex-wrap gap-3 align-items-stretch">
                <div class="map-box" style="flex: 1 1 60%; height: 500px; resize: both; position: relative; min-width: 320px;">
                    <div id="map" style="width: 100%; height: 100%; border-radius: 8px; border: 1px solid #ccc;"></div>
                    <!-- Floating Trail Controls -->
                    <div class="floating-trail-controls">
                        <button type="button" class="btn btn-primary btn-floating" id="viewTrail" title="View Trail">
                            <i class="fas fa-route"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-floating" id="viewHistory" title="View History" onclick="window.open('view_trail_history.php', '_blank')">
                            <i class="fas fa-history"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-floating" id="clearTrail" title="Clear Trail">
                            <i class="fas fa-eraser"></i>
                        </button>
                    </div>
                </div>

                <!-- Vehicle Info Panel -->
                <div class="card vehicle-panel" style="flex: 1 1 38%; min-width: 300px;">
                    <div class="card-header pb-0">
                        <h6 class="mb-0 text-center">Vehicle Status</h6>
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
                        <?php include '../backend/admin_fetch_routes.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include '../modals/admin_edit_route_modal.php'; ?>
        <?php include '../includes/footer.php'; ?>
    </main>
    <!-- Navbar Functionality CSS -->
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
        /* <!-- Map and Vehicle Panel Styles --> */
        .map-box {
            overflow: hidden;
            position: relative;
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
        
        /* Floating Trail Controls */
        .floating-trail-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-floating {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .btn-floating:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.4);
        }
        
        .btn-floating:active {
            transform: translateY(0);
        }
        
        .btn-floating.btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .btn-floating.btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
        }
        
        .btn-floating.btn-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        
        .btn-floating.btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn-floating.btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        /* Ensure Font Awesome icons are visible */
        .btn-floating i {
            font-size: 18px;
            line-height: 1;
        }
        
        .btn-floating .fas,
        .btn-floating .fa {
            display: inline-block !important;
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
        .vehicle-panel .water-fill-full {
            background: linear-gradient(180deg, #28a745 0%, #1e7e34 100%); /* Green */
        }
        .vehicle-panel .water-fill-warning {
            background: linear-gradient(180deg, #ffc107 0%, #e0a800 100%); /* Yellow */
        }
        .vehicle-panel .water-fill-normal {
            background: linear-gradient(180deg, #007bff 0%, #0056b3 100%); /* Blue */
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
<script>
// Global variables
var map;
var allBarangays = [];
var barangayPolygons = {};
var geojsonLoaded = false;
var gpsMarker;
var gpsTrail = [];
var trailPolyline = null;

// Initialize map and load data
window.onload = function () {
    // Define bounding box around Bago City
    var bagoBounds = L.latLngBounds(
        L.latLng(10.4300, 122.7800), // Southwest corner
        L.latLng(10.6500, 123.1000)  // Northeast corner
    );

    // Initialize the map with constraints
    map = L.map('map', {
        center: [10.5379, 122.8333],
        zoom: 13,
        maxBounds: bagoBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 12,
        maxZoom: 18
    });

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add start point marker (Bago City Hall)
    L.marker([10.538274, 122.835230]).addTo(map)
        .bindPopup("<b>Bago City Hall</b>")
        .openPopup();

    // Fetch barangays from backend
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

    // Fetch GeoJSON for polygons
    fetch("../barangay_api/brgy.geojson")
        .then(response => response.json())
        .then(geojson => {
            geojson.features.forEach(feature => {
                var name = feature.properties.name;
                var polygon = L.geoJSON(feature, {
                    style: {
                        color: '#0077B6',
                        fillColor: '#0077B6',
                        fillOpacity: 0.3,
                        weight: 2
                    }
                });
                barangayPolygons[name] = polygon;
            });
            geojsonLoaded = true;
        });

    // Load initial GPS marker and repeat every second
    updateGpsMarker();
    setInterval(updateGpsMarker, 1000);
    
    // Add global function to reset technical warning for testing
    window.resetTechnicalWarning = function() {
        window.technicalWarningShown = false;
        console.log('Technical warning flag reset');
    };
    
    // Initialize trail controls
    initializeTrailControls();
};

// Trail management functions
function updateTrailLine() {
    // Remove existing trail polyline
    if (window.trailPolyline) {
        map.removeLayer(window.trailPolyline);
    }
    
            if (gpsTrail.length > 0) {
            // Add connecting line
            if (gpsTrail.length > 1) {
                window.trailPolyline = L.polyline(gpsTrail, {
                    color: '#ff6b35',
                    weight: 3,
                    opacity: 0.8
                }).addTo(map);
            }
        }
    
    // Save trail to localStorage
    saveTrailToStorage();
}

function clearTrail() {
    // Save trail to history before clearing
    if (gpsTrail.length > 0) {
        saveTrailToHistory();
    }
    
    gpsTrail = [];
    
    // Remove trail polyline
    
    if (window.trailPolyline) {
        map.removeLayer(window.trailPolyline);
        window.trailPolyline = null;
    }
    
    // Clear from localStorage
    localStorage.removeItem('gpsTrail');
    localStorage.removeItem('trailVisible');
    
    console.log('GPS trail cleared');
}

function saveTrailToHistory() {
    const trailName = 'Trail_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-');
    const formData = new FormData();
    formData.append('action', 'save_history');
    formData.append('trail_data', JSON.stringify(gpsTrail));
    formData.append('trail_name', trailName);

    fetch('trail_history.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Trail saved to history:', data.message);
        } else {
            console.error('Error saving trail to history:', data.error);
        }
    })
    .catch(error => {
        console.error('Error saving trail to history:', error);
    });
}

function saveTrailToStorage() {
    try {
        localStorage.setItem('gpsTrail', JSON.stringify(gpsTrail));
        localStorage.setItem('trailVisible', JSON.stringify(window.trailVisible));
    } catch (error) {
        console.log('Error saving trail to localStorage:', error);
    }
}

function loadTrailFromStorage() {
    try {
        const savedTrail = localStorage.getItem('gpsTrail');
        const savedVisibility = localStorage.getItem('trailVisible');
        
        if (savedTrail) {
            gpsTrail = JSON.parse(savedTrail);
            console.log('Loaded trail from storage:', gpsTrail.length, 'points');
        }
        
        if (savedVisibility) {
            window.trailVisible = JSON.parse(savedVisibility);
            console.log('Trail visibility restored:', window.trailVisible);
        }
    } catch (error) {
        console.log('Error loading trail from localStorage:', error);
        gpsTrail = [];
        window.trailVisible = false;
    }
}

function initializeTrailControls() {
    // Load trail from localStorage
    loadTrailFromStorage();
    
    // Update button state based on loaded visibility
    const viewTrailBtn = document.getElementById('viewTrail');
    if (window.trailVisible) {
        viewTrailBtn.classList.remove('btn-primary');
        viewTrailBtn.classList.add('btn-success');
        viewTrailBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        viewTrailBtn.title = 'Hide Trail';
        
        // Show trail if it was visible before
        if (gpsTrail.length > 0) {
            updateTrailLine();
        }
    }
    
    // View trail button
    document.getElementById('viewTrail').addEventListener('click', function() {
        if (!window.trailVisible) {
            // Show trail
            window.trailVisible = true;
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            this.title = 'Hide Trail';
            
            // Update trail line if we have points
            if (gpsTrail.length > 0) {
                updateTrailLine();
            }
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Trail Visible',
                    text: 'GPS trail is now visible on the map.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        } else {
            // Hide trail
            window.trailVisible = false;
            this.classList.remove('btn-success');
            this.classList.add('btn-primary');
            this.innerHTML = '<i class="fas fa-route"></i>';
            this.title = 'View Trail';
            
            // Remove trail polyline
            if (window.trailPolyline) {
                map.removeLayer(window.trailPolyline);
                window.trailPolyline = null;
            }
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Trail Hidden',
                    text: 'GPS trail is now hidden from the map.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
    
    // Clear trail button
    document.getElementById('clearTrail').addEventListener('click', function() {
        // Show confirmation dialog
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Clear Trail',
                text: 'Are you sure you want to clear the GPS trail? This action cannot be undone.',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, clear it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    clearTrail();
                    Swal.fire({
                        icon: 'success',
                        title: 'Trail Cleared',
                        text: 'GPS trail has been cleared from the map.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        } else {
            if (confirm('Are you sure you want to clear the GPS trail? This action cannot be undone.')) {
                clearTrail();
                alert('GPS trail has been cleared from the map.');
            }
        }
    });
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
            if (waterEl) {
                waterEl.style.height = (d.capacity_percent || 0) + '%';
                
                // Update water fill color based on capacity status
                waterEl.className = 'water-fill';
                if (d.capacity_status === 'full') {
                    waterEl.classList.add('water-fill-full');
                } else if (d.capacity_status === 'warning') {
                    waterEl.classList.add('water-fill-warning');
                } else {
                    waterEl.classList.add('water-fill-normal');
                }
            }

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

document.addEventListener('DOMContentLoaded', initVehiclePanel);
window.addEventListener('load', initVehiclePanel);

// View route button handler
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.view-route');
    buttons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const barangayName = button.getAttribute('data-barangay');
            const barangay = allBarangays.find(b => b.barangay === barangayName);
            // Remove any existing polygons
            Object.values(barangayPolygons).forEach(poly => {
                if (map.hasLayer(poly)) map.removeLayer(poly);
            });
            // Show geo-fence polygon
            if (barangayPolygons[barangayName]) {
                barangayPolygons[barangayName].addTo(map);
                map.fitBounds(barangayPolygons[barangayName].getBounds());
            }
            if (!barangay || !barangay.latitude || !barangay.longitude) {
                alert(`Coordinates not found for ${barangayName}`);
                return;
            }
            const startPoint = [10.538274, 122.835230]; // Bago City Hall
            const endPoint = [parseFloat(barangay.latitude), parseFloat(barangay.longitude)];
            // Remove existing route controls
            map.eachLayer(layer => {
                if (layer instanceof L.Routing.Control) {
                    map.removeControl(layer);
                }
            });
            // Add routing control
            const routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(startPoint[0], startPoint[1]),
                    L.latLng(endPoint[0], endPoint[1])
                ],
                routeWhileDragging: false,
                lineOptions: { styles: [{ color: 'green', weight: 4 }] },
                createMarker: function () { return null; },
                show: false,
                addWaypoints: false,
                draggableWaypoints: false
            }).addTo(map);
            // Fit bounds when route is found
            routingControl.on('routesfound', function (e) {
                const routes = e.routes;
                if (routes.length > 0) {
                    map.fitBounds(routes[0].coordinates);
                }
            });
        });
    });
});

// Edit route button handler
document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.edit-route-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const routeId = btn.getAttribute('data-route-id');
            const currentEnd = btn.getAttribute('data-end-point');
            const currentStart = btn.getAttribute('data-start-point');
            document.getElementById('editRouteId').value = routeId;
            document.getElementById('currentStartPoint').value = currentStart;
            document.getElementById('currentEndPoint').value = currentEnd;
            document.getElementById('editEndPoint').value = currentEnd;
            const modal = new bootstrap.Modal(document.getElementById('editRouteModal'));
            modal.show();
        });
    });
});

// Tooltip initialization
document.addEventListener('DOMContentLoaded', () => {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = { damping: '0.5' }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
});

// GPS marker update function
function updateGpsMarker() {
    fetch('get_latest_gps.php')
        .then(res => res.json())
        .then(data => {
            // Debug: Log the data to console
            console.log('GPS Data:', data);
            console.log('Technical warning:', data.technical_warning);
            console.log('Latest coords:', data.latest_coords);
            console.log('Coords count:', data.coords_count);
            
            // Check if coordinates haven't changed for 10 seconds (frontend time tracking)
            if (data.latest_coords) {
                const currentCoords = data.latest_coords.latitude + ',' + data.latest_coords.longitude;
                
                if (!window.lastCoords) {
                    window.lastCoords = currentCoords;
                    window.coordsStartTime = Date.now();
                } else if (window.lastCoords === currentCoords) {
                    // Same coordinates, check if it's been 1 day (86400 seconds)
                    const timeDiff = (Date.now() - window.coordsStartTime) / 1000;
                    console.log('Same coordinates for', timeDiff, 'seconds');
                    
                    if (timeDiff > 86400 && !window.technicalWarningShown) {
                        console.log('Technical warning triggered! Same coords for', timeDiff, 'seconds');
                        window.technicalWarningShown = true;
                        
                        // Try to show SweetAlert, fallback to regular alert
                        try {
                            if (typeof Swal !== 'undefined' && Swal.fire) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Technical Problem Detected',
                                    text: 'A possible unpredictable technical problem on Vehicle 1 is determined.',
                                    confirmButtonColor: '#ff9800',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                throw new Error('SweetAlert not available');
                            }
                        } catch (error) {
                            console.log('Falling back to regular alert:', error);
                            alert('A possible unpredictable technical problem on Vehicle 1 is determined.');
                        }
                    }
                } else {
                    // Coordinates changed, reset tracking
                    window.lastCoords = currentCoords;
                    window.coordsStartTime = Date.now();
                    window.technicalWarningShown = false;
                    console.log('Coordinates changed, resetting tracking');
                }
            }
            
            // Remove previous GPS marker
            if (window.gpsMarker && map.hasLayer(window.gpsMarker)) {
                map.removeLayer(window.gpsMarker);
            }
            window.gpsMarker = null;
            if (!data.gps_points || !Array.isArray(data.gps_points) || data.gps_points.length === 0) return;
            var point = data.gps_points[0]; // Only the latest
            if (!point.latitude || !point.longitude) return;
            const latLng = [point.latitude, point.longitude];
            // Always update marker
            let icon = L.icon({
                iconUrl: '../assets/img/gps_icon.png',
                iconSize: [30, 30],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });
            window.gpsMarker = L.marker(latLng, { icon: icon })
                .addTo(map)
                .bindPopup(`📍 GPS Current Location`);
            
            // Add point to GPS trail (always track, visibility controlled by button)
            gpsTrail.push(latLng);
            
            // Keep only last 100 points to prevent memory issues
            if (gpsTrail.length > 100) {
                gpsTrail.shift();
            }
            
            // Update trail line on map if trail is visible
            if (window.trailVisible) {
                updateTrailLine();
            }

            // Only run entry/exit logic if GPS position changed
            if (window.lastGpsLat === latLng[0] && window.lastGpsLng === latLng[1]) {
                // GPS did not move, skip popups
                return;
            }
            window.lastGpsLat = latLng[0];
            window.lastGpsLng = latLng[1];

            // Track current barangay and previous barangay
            let insideAny = false;
            let currentBrgy = null;
            
            // Check which barangay the GPS is currently inside
            Object.keys(barangayPolygons).forEach(function(barangayName) {
                var polygon = barangayPolygons[barangayName];
                if (polygon && geojsonLoaded) {
                    var inside = false;
                    polygon.eachLayer(function(layer) {
                        if (layer instanceof L.Polygon) {
                            // Accurate point-in-polygon check
                            if (layer.contains && layer.contains(latLng)) {
                                inside = true;
                            } else {
                                // Fallback for older Leaflet: manual point-in-polygon
                                var polyLatLngs = layer.getLatLngs()[0];
                                var x = latLng[1], y = latLng[0]; // x: lng, y: lat
                                var insidePoly = false;
                                for (var i = 0, j = polyLatLngs.length - 1; i < polyLatLngs.length; j = i++) {
                                    var xi = polyLatLngs[i].lng, yi = polyLatLngs[i].lat;
                                    var xj = polyLatLngs[j].lng, yj = polyLatLngs[j].lat;
                                    var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi + 0.0000001) + xi);
                                    if (intersect) insidePoly = !insidePoly;
                                }
                                if (insidePoly) inside = true;
                            }
                        }
                    });
                    if (inside) {
                        insideAny = true;
                        currentBrgy = barangayName;
                    }
                }
            });

            // Handle entry/exit logic
            if (insideAny) {
                // GPS is inside a barangay
                if (window.lastBrgy !== currentBrgy) {
                    // Vehicle moved to a different barangay
                    if (window.lastBrgy && barangayPolygons[window.lastBrgy]) {
                        // Show exit popup for previous barangay first
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Vehicle 1 Exited',
                                text: `The Vehicle 1 has exited from ${window.lastBrgy}.`,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Show entry popup for new barangay after exit popup is closed
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Vehicle 1 Entered',
                                        text: `The Vehicle 1 has entered in ${currentBrgy}.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert(`The Vehicle 1 has entered in ${currentBrgy}.`);
                                }
                            });
                        } else {
                            alert(`The Vehicle 1 has exited from ${window.lastBrgy}.`);
                            alert(`The Vehicle 1 has entered in ${currentBrgy}.`);
                        }
                    } else {
                        // First time entering any barangay
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Vehicle 1 Entered',
                                text: `The Vehicle 1 has entered in ${currentBrgy}.`,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`The Vehicle 1 has entered in ${currentBrgy}.`);
                        }
                    }
                    window.lastBrgy = currentBrgy;
                }
                // If GPS is still in the same barangay, do nothing (no repeated prompts)
            } else {
                // GPS is not inside any barangay
                if (window.lastBrgy) {
                    // Show exit popup when leaving a barangay
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Vehicle 1 Exited',
                            text: `The Vehicle 1 has exited from ${window.lastBrgy}.`,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`The Vehicle 1 has exited from ${window.lastBrgy}.`);
                    }
                    window.lastBrgy = null;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching GPS data:', error);
        });
}
</script>
</body>
</html>
