<?php
session_start();

// Check if the user is logged in client
if (!isset($_SESSION['client_id'])) {
    // Redirect to the login page if not logged in
    header("Location: ../login_page/sign-in.php");
    exit();
}

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
        <?php include '../includes/conn.php'; ?>

        <?php if (isset($_SESSION['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
    <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>


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
            <h2 class="text-center">Bago City Map</h2>
            
            <!-- Map Box Container -->
            <div class="map-box" style="width: 100%; height: 400px; resize: both;">
                <div id="map" style="width: 100%; height: 100%; border-radius: 8px; border: 1px solid #ccc;"></div>
            </div>

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
<?php

// Fetch all routes from the route table
$sql = "SELECT r.*, d.first_name, d.last_name, w.vehicle_name, w.plate_no, w.vehicle_capacity
        FROM route_table r
		LEFT JOIN driver_table d ON r.driver_id = d.driver_id
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
</body>
</html>

<script>
    // Global variables
    var map;
    var allBarangays = [];

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
    }
    );

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
    };

    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.view-route');

        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();

                const barangayName = button.getAttribute('data-barangay');
                const barangay = allBarangays.find(b => b.barangay === barangayName);

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
                    createMarker: function () { return null; } // Hide default markers
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
    const detailsButtons = document.querySelectorAll('.details-route-btn');
    detailsButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('detailsVehicleName').textContent = btn.getAttribute('data-vehicle') || 'N/A';
            document.getElementById('detailsPlateNumber').textContent = btn.getAttribute('data-plate') || 'N/A';
            document.getElementById('detailsVehicleType').textContent = btn.getAttribute('data-type') || 'N/A';
            document.getElementById('detailsDriverName').textContent = btn.getAttribute('data-driver') || 'N/A';
            document.getElementById('detailsStartPoint').textContent = btn.getAttribute('data-start') || 'N/A';
            document.getElementById('detailsEndPoint').textContent = btn.getAttribute('data-end') || 'N/A';
        });
    });
});


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



