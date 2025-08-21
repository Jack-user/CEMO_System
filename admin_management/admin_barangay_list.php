<?php
// dashboard.php (MAIN FILE)
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

     // Create a red icon
const redIcon = new L.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

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

            if (!marker) {
              marker = L.marker([lat, lng], { icon: redIcon }).addTo(map);
            } else {
              marker.setLatLng([lat, lng]);
              marker.setIcon(redIcon);
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



