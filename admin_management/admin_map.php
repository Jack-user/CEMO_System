<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}
<<<<<<< HEAD


$page_title = "Bago City Map";
include '../includes/header.php'; // Includes the head section and styles
    ?>
    <body class="g-sidenav-show bg-gray-200">
        <!-- Sidebar -->
        <?php include '../sidebar/admin_sidebar.php'; ?>

        <!-- Main Content Wrapper -->
        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
            <!-- Navbar -->
            <?php include '../includes/navbar.php'; ?>
            <?php include '../includes/conn.php'; ?>

            <?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
        <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

            <!-- Main Content -->

        <?php
        // --- Fetch latest coordinates
        $currentLocation = null;
        $sql = "SELECT latitude, longitude FROM gps_location ORDER BY location_id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $currentLocation = $result->fetch_assoc();
        }
        ?>

        <!-- Fetch all barangays -->
        <?php
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
        ?>


        <!-- Page Content -->
        <div class="container mt-4">
=======
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
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42
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
<<<<<<< HEAD
<?php

// Fetch all routes from the route table
$sql = "SELECT r.*, w.vehicle_name 
        FROM route_table r
        LEFT JOIN waste_service_table w ON r.route_id = w.route_id";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
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
                   class="badge badge-sm bg-gradient-warning view-route"
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
                class="badge badge-sm bg-gradient-success edit-route-btn"
                data-bs-toggle="modal"
                data-route-id="<?= $row['route_id']; ?>"
                data-end-point="<?= htmlspecialchars($row['end_point']); ?>"
                data-start-point="<?= htmlspecialchars($row['start_point']); ?>">
                Edit <span class="material-symbols-rounded opacity-10" style="font-size: 0.9rem;">Edit</span>
            </a>
        </div>
        </td>
    </tr>
<?php
    endwhile;
else:
?>
    <tr>
        <td colspan="3" class="text-center text-secondary">No routes found.</td>
    </tr>
<?php endif; ?>

    <!-- Edit Route Modal -->
<div class="modal fade" id="editRouteModal" tabindex="-1" aria-labelledby="editRouteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="admin_update_route.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editRouteLabel">Edit Route End Point</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="route_id" id="editRouteId">
            <div class="mb-3">
                <label for="currentStartPoint" class="form-label">Current Start Point</label>
                <input type="text" id="currentStartPoint" class="form-control" disabled>
            </div>
            

          <div class="mb-3">
              <label for="currentEndPoint" class="form-label">Current End Point</label>
              <input type="text" id="currentEndPoint" class="form-control" disabled>
            </div>

            <div class="mb-3">
              <label for="editEndPoint" class="form-label">Select New End Point</label>
              <select name="end_point" id="editEndPoint" class="form-select" required>
                <?php foreach ($allBarangays as $barangay): ?>
                  <option value="<?= htmlspecialchars($barangay['barangay']); ?>">
                    <?= htmlspecialchars($barangay['barangay']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update Route</button>
        </div>
      </div>
      
    </form>
  </div>
</div>
</tbody>

=======
                        <?php include '../backend/admin_fetch_routes.php'; ?>
                    </tbody>
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42
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
<<<<<<< HEAD
    }
    );
    // Initial marker placeholder
let gpsMarker;
function updateGpsMarker() {
    fetch('get_latest_gps.php')
        .then(res => res.json())
        .then(data => {
            if (!data.latitude || !data.longitude) return;

            const latLng = [data.latitude, data.longitude];

            if (gpsMarker) {
                gpsMarker.setLatLng(latLng);
            } else {
                gpsMarker = L.marker(latLng, {
                    icon: L.icon({
                        iconUrl: '../assets/img/gps-icon.png', // Optional: Use your own icon
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    })
                }).addTo(map).bindPopup("📍 Current GPS Location");
                // Don't open the popup automatically and don't pan/zoom to it
                // gpsMarker.openPopup(); <-- removed this line
            }
        });
}

// Load initial and repeat every 10 seconds
updateGpsMarker();
setInterval(updateGpsMarker, 1000);

=======
    });
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42

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

<<<<<<< HEAD

</script>

<!-- Abaunan 10.525313 122.992415
Alainza 10.47393 122.92993 
Atipuluan 10.51083 122.95626
Bacong-Montilla 10.51895 123.03452
Bagroy 10.47718 122.87212                   
Balingasag 10.53161 122.84595                  
Binubuhan 10.45755 123.00718                   
Busay 10.53718 122.88822                  
Calumangan 10.56009 122.87680                 
Caridad 10.48198 122.90567                   
Don Jorge L. Araneta 10.47642 122.94615              
Dulao 10.54916 122.95165                 
Ilijan 10.45300 123.05486                 
Lag Asan 10.530060 122.838575                  
Ma ao 10.49019 122.99165                
Mailum 10.46211 123.04920                   
Malingin 10.49395 122.91783                   
Napoles 10.51267 122.89781                   
Pacol 10.49507 122.86697                 
Población 10.54115 122.83539                 
Sagasa 10.46983 122.89283                 
Tabunan 10.57625 122.93727                 
Taloc 10.58730 122.90942                 
Sampinit 10.54426 122.85341                    -->
=======
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42
