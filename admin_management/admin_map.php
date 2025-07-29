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

    <style>
        .map-box { overflow: hidden; position: relative; }
        .map-box::after {
            content: ''; position: absolute; right: 0; bottom: 0; width: 5px; height: 5px;
            background: #000; cursor: nwse-resize; z-index: 999;
        }
    </style>

    <script src="map_script.js"></script>
</body>
</html>
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
<script>
    // Global variables
    var map;
    var allBarangays = [];
    var barangayPolygons = {};
    var geojsonLoaded = false;

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
        maxBoundsViscosity: 1.0, // Prevents panning outside bounds
        minZoom: 12,               // Prevent zooming out too far
        maxZoom: 18                // Optional zoom limit
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
    };

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

                // Show geo-fence polygon for Sum ag or Sum ag2
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