<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}
$page_title = "Bago City Map";
include '../includes/header.php';
?>

<body class="g-sidenav-show bg-gray-200">
    <?php include '../sidebar/admin_sidebar.php'; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>
        <?php include '../includes/conn.php'; ?>

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

        <div class="container mt-3">
            <h2 class="text-center">Bago City Map</h2>
            <div class="map-box" style="width: 100%; height: 500px; resize: both;">
                <div id="map" style="width: 100%; height: 100%; border-radius: 8px; border: 1px solid #ccc;"></div>
            </div>

            <div class="vehicle-routes flex-fill" style="min-width: 300px; overflow-x: auto;">
  <h4 class="text-center mb-3">Vehicle Routes</h4>
  <div style="min-width: 600px;">
    <table class="table align-items-center table-flush">
      <thead class="thead-light">
        <tr>
          <th class="text-center">Vehicle List</th>
          <th class="text-center">Route</th>
          <th class="text-center">Actions</th>
          <th class="text-center">Tools</th>
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

    <!-- Scripts -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>

    <!-- Map Styles -->
    <style>
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
    </style>

<!-- Main Map and UI Scripts -->
<script>
// Global variables
var map;
var allBarangays = [];
var barangayPolygons = {};
var geojsonLoaded = false;
var gpsMarker;

// Initialize map and load data
window.onload = function () {
    // Define bounding box around Bago City
    var bagoBounds = L.latLngBounds(
        L.latLng(10.4300, 122.7800), // Southwest corner
        L.latLng(10.6500, 123.1000)  // Northeast corner
    );

    // Initialize the map with constraints
    // Disable default zoom and re-add in top right
    const map = L.map('map', {
  center: [10.536421, 122.834205], // your default center (Bago City Hall)
  zoom: 13,                        // your default zoom
  minZoom: 11,                     // ⬅️ prevent too far zoom-out
  maxZoom: 18                      // ⬅️ optional: limit zoom-in
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
                        .bindPopup("<b>" + barangay.barangay + "</b><br>Bago City");
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
};

// View route button handler
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.view-route');
    buttons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const barangayName = button.getAttribute('data-barangay');
            const barangay = allBarangays.find(b => b.barangay === barangayName);
            // Remove any existing polygons map
            Object.values(barangayPolygons).forEach(poly => {
                if (map.hasLayer(poly)) map.removeLayer(poly);
            });
            // Show geo-fence polygon
            if (barangayPolygons[barangayName]) {
                barangayPolygons[barangayName].addTo(map);
                map.fitBounds(barangayPolygons[barangayName].getBounds());
            }
            if (!barangay || !barangay.latitude || !barangay.longitude) {
                alert("Coordinates not found for " + barangayName);
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
// Show up to 5 recent GPS points as markers
function updateGpsMarker() {
    fetch('get_latest_gps.php')
        .then(res => res.json())
        .then(data => {
            // Debug: Log the data to console
            // console.log('GPS Data:', data);

            // Check if coordinates haven't changed for 1 day (86400 seconds)
            if (data.latest_coords) {
                const currentCoords = data.latest_coords.latitude + ',' + data.latest_coords.longitude;

                if (!window.lastCoords) {
                    window.lastCoords = currentCoords;
                    window.coordsStartTime = Date.now();
                } else if (window.lastCoords === currentCoords) {
                    const timeDiff = (Date.now() - window.coordsStartTime) / 1000;
                    if (timeDiff > 10 && !window.technicalWarningShown) {
                        window.technicalWarningShown = true;
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
                            alert('A possible unpredictable technical problem on Vehicle 1 is determined.');
                        }
                    }
                } else {
                    window.lastCoords = currentCoords;
                    window.coordsStartTime = Date.now();
                    window.technicalWarningShown = false;
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
                .bindPopup("📍 GPS Current Location");

            // Only run entry/exit logic if GPS position changed
            if (window.lastGpsLat === latLng[0] && window.lastGpsLng === latLng[1]) {
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
                                    var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / ((yj - yi) + 0.0000001) + xi);
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
                if (window.lastBrgy !== currentBrgy) {
                    if (window.lastBrgy && barangayPolygons[window.lastBrgy]) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Vehicle 1 Exited',
                                text: "The Vehicle 1 has exited from " + window.lastBrgy + ".",
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Vehicle 1 Entered',
                                        text: "The Vehicle 1 has entered in " + currentBrgy + ".",
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert("The Vehicle 1 has entered in " + currentBrgy + ".");
                                }
                            });
                        } else {
                            alert("The Vehicle 1 has exited from " + window.lastBrgy + ".");
                            alert("The Vehicle 1 has entered in " + currentBrgy + ".");
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Vehicle 1 Entered',
                                text: "The Vehicle 1 has entered in " + currentBrgy + ".",
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert("The Vehicle 1 has entered in " + currentBrgy + ".");
                        }
                    }
                    window.lastBrgy = currentBrgy;
                }
            } else {
                if (window.lastBrgy) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Vehicle 1 Exited',
                            text: "The Vehicle 1 has exited from " + window.lastBrgy + ".",
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert("The Vehicle 1 has exited from " + window.lastBrgy + ".");
                    }
                    window.lastBrgy = null;
                }
            }
        });
}
</script>
</body>
</html>