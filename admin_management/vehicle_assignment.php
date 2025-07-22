<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login_page/sign-in.php");
  exit();
}

// 🚛 Fetch Vehicle Assignments (no maintenance)
$schedules = [];
$sql = "SELECT 
          v.vehicle_name,
          vehicle_type,
          v.vehicle_capacity,
          b.barangay,
          t.day,
          t.status 
        FROM service_assignment_table s
        LEFT JOIN schedule_table t ON s.schedule_id = t.schedule_id
        LEFT JOIN waste_service_table v ON s.waste_service_id = v.waste_service_id
        LEFT JOIN barangays_table b ON s.brgy_id = b.brgy_id
        LEFT JOIN maintenance_table m ON v.waste_service_id = m.waste_service_id
        ORDER BY v.vehicle_name, FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $vehicle = $row['vehicle_name'];
    $barangay = $row['barangay'];
    $key = $vehicle . '|' . $barangay;

    $schedules[$key]['vehicle_name'] = $vehicle;
    $schedules[$key]['vehicle_type'] = $row['vehicle_type'];
    $schedules[$key]['vehicle_capacity'] = $row['vehicle_capacity'];
    $schedules[$key]['barangay'] = $barangay;
    $schedules[$key]['days'][$row['day']] = $row['status'];
    $schedules[$key]['date_time'] = isset($row['maintenance_date']) ? $row['maintenance_date'] : null;
  }
}

// 🗓️ Fetch Waste Collection Daily Schedule (optional logic)
$wasteCollectionSchedules = [];
$sql2 = "SELECT w.vehicle_name, s.day, s.status 
         FROM schedule_table s 
         LEFT JOIN waste_service_table w ON w.waste_service_id = s.waste_service_id 
         ORDER BY w.vehicle_name";

$result2 = $conn->query($sql2);
if ($result2 && $result2->num_rows > 0) {
  while ($row = $result2->fetch_assoc()) {
    $wasteCollectionSchedules[$row['vehicle_name']][$row['day']] = $row['status'];
  }
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Vehicle Assignment & Waste Schedule</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>
<body>
<?php include '../sidebar/admin_sidebar.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-start mt-2 mb-4">
      <div class="dropdown">
  <button class="btn btn-success dropdown-toggle" 
          type="button" 
          id="dropdownMenuButton" 
          data-bs-toggle="dropdown" 
          aria-expanded="false">
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
      <!-- 🗓️ Waste Collection Daily Schedule Card -->
      <!-- 🚛 Vehicle Assignment Card -->
<div class="col-12 mb-8">
        <div class="card shadow-lg">
          <div class="card-header text-white" style="background: linear-gradient(90deg,rgb(61, 144, 238), #4364f7);">
  <h5 class="text-center text-uppercase font-weight-bold mb-0">Waste Collection Daily Schedule</h5>
</div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table table-bordered align-items-center mb-0 text-center">
                <thead class="bg-light">
                  <tr>
                    <th>Vehicle</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                    <th>Saturday</th>
                    <th>Sunday</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($wasteCollectionSchedules as $vehicle => $days): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($vehicle) ?></strong></td>
                      <?php
                        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($weekdays as $day) {
                          echo '<td>' . (!empty($days[$day]) ? htmlspecialchars($days[$day]) : 'Vacant') . '</td>';
                        }
                      ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 🚛 Vehicle Assignment Card -->
      <div class="col-12 col-md-10 col-lg-8 mx-auto mt-4 mb-4">
        <div class="card shadow-lg">
          <div class="card-header text-white" style="background: linear-gradient(90deg,rgb(81, 206, 168),rgb(64, 189, 172));">
  <h5 class="text-center text-uppercase font-weight-bold mb-0">Vehicle Assignments</h5>
</div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table table-bordered align-items-center mb-0 text-center">
                <thead class="bg-light">
                  <tr>
                    <th>Vehicle</th>
                    <th>Vehicle Type</th>
                    <th>Capacity</th>
                    <th>Area</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($schedules as $key => $info): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($info['vehicle_name']) ?></strong></td>
                      <td><?= htmlspecialchars($info['vehicle_type']) ?></td>
                      <td><?= htmlspecialchars($info['vehicle_capacity']) ?></td>
                      <td><?= htmlspecialchars($info['barangay']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/core/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  // ✅ Auto-hide alerts (already fixed earlier)
  const successAlert = document.getElementById("success-alert");
  if (successAlert) {
    setTimeout(() => {
      successAlert.style.opacity = "1";
    }, 300);
    setTimeout(() => {
      successAlert.style.opacity = "0";
      setTimeout(() => {
        successAlert.remove();
      }, 500);
    }, 2300);
  }

  const errorAlert = document.getElementById("error-alert");
  if (errorAlert) {
    setTimeout(() => {
      errorAlert.style.opacity = "1";
    }, 300);
    setTimeout(() => {
      errorAlert.style.opacity = "0";
      setTimeout(() => {
        errorAlert.remove();
      }, 500);
    }, 2300);
  }

  // ✅ SweetAlert for deletion confirmation
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const name = this.dataset.name;
      const href = this.getAttribute('href');

      Swal.fire({
        title: `Delete "${name}"?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    });
  });
});
</script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>