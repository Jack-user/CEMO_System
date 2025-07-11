<?php
session_start();
include '../includes/header.php'; // Includes the head section and styles

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the login page if not logged in
    header("Location: ../login_page/sign-in.php");
    exit();
}

include '../includes/conn.php'; // Include your database connection file

// Test the database connection (optional, for debugging)
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch barangay data from the database
$query = "SELECT brgy_id, barangay, latitude, longitude, facebook_link, link_text FROM barangays_table"; // Ensure all required columns are included
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brgy'])) {
    $brgy_id = $_POST['brgy_id'];
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $facebook_link = mysqli_real_escape_string($conn, $_POST['facebook_link']);
    $link_text = mysqli_real_escape_string($conn, $_POST['link_text']);

    // Validate latitude and longitude
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        echo "<script>alert('Invalid latitude or longitude!');</script>";
        exit();
    }

    $update_query = "UPDATE barangays_table 
                    SET barangay = '$barangay', latitude = '$latitude', longitude = '$longitude', facebook_link = '$facebook_link', link_text = '$link_text'
                    WHERE brgy_id = '$brgy_id'";

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Barangay details updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating barangay details: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Barangay List</title>
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css " />
</head>
<body class="g-sidenav-show bg-gray-200">
  <!-- Sidebar -->
  <?php include '../sidebar/admin_sidebar.php'; ?>
  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>
    <!-- Page Content -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card shadow-lg">
            <div class="card-header bg-gradient-primary text-white">
              <h5 class="text-center text-uppercase font-weight-bold mb-0">Barangay List</h5>
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
                          <div class="d-flex align-items-center px-3 py-2">
                            <div>
                              <img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="<?= htmlspecialchars($row['barangay']); ?>">
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['barangay']); ?></h6>
                            </div>
                          </div>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <?php if (!empty($row['facebook_link']) && !empty($row['link_text'])): ?>
                            <a href="<?= htmlspecialchars($row['facebook_link']); ?>" target="_blank" class="text-primary text-xs font-weight-bold" style="text-decoration: none;">
                              <?= htmlspecialchars($row['link_text']); ?>
                            </a>
                          <?php else: ?>
                            <span class="text-xs text-secondary">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                            <a href="#" class="text-info text-xs font-weight-bold view-location-btn"
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
                          <!-- Edit Modal -->
                          <div class="modal fade" id="editModal<?= $row['brgy_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="editModalLabel">Edit Barangay Details</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <form method="POST" action="">
                                    <input type="hidden" name="brgy_id" value="<?= htmlspecialchars($row['brgy_id']); ?>">
                                    <div class="mb-3">
                                      <label for="barangay" class="form-label">Barangay Name</label>
                                      <input type="text" class="form-control" id="barangay" name="barangay" value="<?= htmlspecialchars($row['barangay']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                      <label for="latitude" class="form-label">Latitude</label>
                                      <input type="text" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($row['latitude'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                      <label for="longitude" class="form-label">Longitude</label>
                                      <input type="text" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($row['longitude'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                      <label for="facebook_link" class="form-label">Facebook Link</label>
                                      <input type="url" class="form-control" id="facebook_link" name="facebook_link" value="<?= htmlspecialchars($row['facebook_link'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                      <label for="link_text" class="form-label">Link Text</label>
                                      <input type="text" class="form-control" id="link_text" name="link_text" value="<?= htmlspecialchars($row['link_text'] ?? ''); ?>" required>
                                    </div>
                                    <button type="submit" name="update_brgy" class="btn btn-primary">Save Changes</button>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </div>
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

    <!-- Leaflet Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapModalLabel">Barangay Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="locationMap" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
  </div>
</div>






    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
  </main>
  <!-- Core JS Files -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
      <!-- Leaflet Map JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Leaflet Routing Machine CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

  <script>
document.addEventListener('DOMContentLoaded', function () {
    let map;
    let barangayPolygons = {};
    let marker;
    let mapInitialized = false;

    const bagoBounds = L.latLngBounds(
        L.latLng(10.4300, 122.7800),
        L.latLng(10.6500, 123.1000)
    );

    function initializeMap() {
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

        const barangayGeoFences = {
            <?php
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)) {
                if (!empty($row['latitude']) && !empty($row['longitude'])) {
                    $lat = floatval($row['latitude']);
                    $lng = floatval($row['longitude']);
                    $brgyName = addslashes($row['barangay']);
                    echo "'$brgyName': [
                        [$lat, $lng],
                        [" . ($lat + 0.001) . ", $lng],
                        [" . ($lat + 0.001) . ", " . ($lng + 0.001) . "],
                        [$lat, " . ($lng + 0.001) . "],
                        [$lat, $lng]
                    ],";
                }
            }
            ?>
        };

        for (const [brgyName, coordinates] of Object.entries(barangayGeoFences)) {
            const polygon = L.polygon(coordinates, {
                color: 'blue',
                fillColor: '#3388ff',
                fillOpacity: 0.2,
                weight: 1.5,
                interactive: false
            }).addTo(map);
            polygon.bindPopup(`<b>${brgyName}</b> Geo-Fence`);
            barangayPolygons[brgyName] = polygon;
            polygon.setStyle({opacity: 0}); // Hide all by default
            polygon.remove(); // Remove from map by default
        }

        mapInitialized = true;
    }

    // Show only the selected barangay's geo-fence
    function showBarangayGeoFence(name) {
        // Remove all polygons from map
        for (const [brgyName, polygon] of Object.entries(barangayPolygons)) {
            map.removeLayer(polygon);
        }
        // Add and highlight only the selected polygon
        if (barangayPolygons[name]) {
            barangayPolygons[name].addTo(map);
            barangayPolygons[name].setStyle({
                fillOpacity: 0.6,
                color: 'red',
                weight: 2,
                opacity: 1
            });
        }
    }

    // Bootstrap modal event
    var mapModal = document.getElementById('mapModal');
    mapModal.addEventListener('shown.bs.modal', function (event) {
        let button = document.querySelector('.view-location-btn[data-bs-target="#mapModal"].active-trigger');
        if (!button) {
            button = document.querySelector('.view-location-btn[data-bs-target="#mapModal"]');
        }
        if (!mapInitialized) {
            initializeMap();
        }
        if (button) {
            const lat = parseFloat(button.getAttribute('data-lat'));
            const lng = parseFloat(button.getAttribute('data-lng'));
            const name = button.getAttribute('data-name');

            map.setView([lat, lng], 14);

            showBarangayGeoFence(name);

            if (!marker) {
                marker = L.marker([lat, lng]).addTo(map);
            } else {
                marker.setLatLng([lat, lng]);
            }

            marker.bindPopup(`<b>${name}</b>`).openPopup();
        }
        setTimeout(() => {
            map.invalidateSize();
        }, 200);
    });

    // Track which button was clicked
    document.querySelectorAll('.view-location-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-location-btn').forEach(b => b.classList.remove('active-trigger'));
            this.classList.add('active-trigger');
        });
    });

    // Remove marker and geo-fence highlight when modal is closed
    mapModal.addEventListener('hidden.bs.modal', function () {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        // Remove all polygons from map
        for (const [brgyName, polygon] of Object.entries(barangayPolygons)) {
            map.removeLayer(polygon);
        }
    });
});
</script>

</body>
</html>



