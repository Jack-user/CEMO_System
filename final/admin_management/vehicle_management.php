<?php
session_start();
include '../includes/header.php'; // Includes the head section and styles

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
  // Redirect to the login page if not logged in
  header("Location: ../login_page/sign-in.php");
  exit();
}

// Database connection
include '../includes/conn.php'; // Ensure this file contains your database connection logic

// Fetch vehicle data from the database
$query = "SELECT * FROM waste_service_table";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Vehicle Management Dashboard
  </title>
  <!-- Fonts and Icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>
<body>
  <!-- Sidebar -->
  <?php include '../sidebar/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <?php
// Fetch vehicle list with driver names
$sql = "SELECT v.*, CONCAT(d.first_name, ' ', d.last_name) AS driver_name,
        date_time,  CONCAT('Start Point: ', r.start_point, ' - End Point: ', r.end_point) AS route_info
        FROM waste_service_table v
        LEFT JOIN route_table r ON v.route_id = r.route_id
        LEFT JOIN maintenance_table m ON v.maintenance_id = m.maintenance_id
        LEFT JOIN driver_table d ON v.driver_id = d.driver_id";

$result = $conn->query($sql);
?>

    <!-- Page Content -->
    <div class="container-fluid py-4">
    <div class="d-flex justify-content-start mt-2 mb-0">
      <div class="dropdown">
        <button class="btn btn-success btn-lg fw-bold shadow-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          📅 View
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item" href="vehicle_assignment.php">Vehicle Assignment</a></li>
          <li><a class="dropdown-item" href="waste_service_sched.php">Waste Collection Schedule</a></li>
          <li><a class="dropdown-item" href="vehicle_management.php">Vehicle Management</a></li>
        </ul>
      </div>
    </div>
      <div class="row">
        <div class="col-12">
          <div class="card shadow-lg">
            <div class="card-header bg-gradient-primary text-white">
              <h5 class="text-center text-uppercase font-weight-bold mb-0">Vehicle List</h5>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Vehicle Name</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Driver's Name</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Plate Number</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Truck Capacity</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Maintenance Schedule</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Route Information</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($result->num_rows > 0): ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center px-3 py-2">
                              <div>
                                <img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="vehicle">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($row['vehicle_name']); ?></h6>
                              </div>
                            </div>
                          </td>
                          <td class="align-middle text-sm">
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['driver_name']); ?></p>
                          </td>
                          <td class="align-middle text-sm">
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['plate_no']); ?></p>
                          </td>
                          <td class="align-middle text-sm">
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['vehicle_capacity']); ?></p>
                          </td>
                          <td class="align-middle text-sm">
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['date_time']); ?></p>
                          </td>
                          <td class="align-middle text-sm">
                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['route_info']); ?></p>
                          </td>
                          <td class="align-middle text-center">
                            <a href="edit_vehicle.php?id=<?php echo $row['waste_service_id']; ?>" class="btn btn-link text-info px-2 py-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Vehicle">
                              <i class="material-symbols-rounded fs-5">edit</i>
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="8" class="text-center text-secondary">No vehicles found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
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
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Github Buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Material Dashboard JS -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>

