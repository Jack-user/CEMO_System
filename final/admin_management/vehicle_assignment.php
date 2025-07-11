<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php'; // Add DB connection

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login_page/sign-in.php");
  exit();
}

// Fetch assignments from service_assignment_table
$schedules = [];
$sql = "SELECT 
          v.vehicle_name,
          vehicle_type,
          v.vehicle_capacity,
          b.barangay,
          t.day,
          t.status 
        FROM service_assignment_table s
        LEFT JOIN schedule_table t ON s.schedule_id = s.schedule_id
        LEFT JOIN waste_service_table v ON s.waste_service_id = v.waste_service_id
        LEFT JOIN barangays_table b ON s.brgy_id = b.brgy_id
        ORDER BY v.vehicle_name, FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $vehicle = $row['vehicle_name'];
    $barangay = $row['barangay'];
    $key = $vehicle . '|' . $barangay; // Grouping key: vehicle + barangay

    $schedules[$key]['vehicle_name'] = $vehicle;
    $schedules[$key]['vehicle_type'] = $row['vehicle_type'];
    $schedules[$key]['vehicle_capacity'] = $row['vehicle_capacity'];
    $schedules[$key]['barangay'] = $barangay;
    $schedules[$key]['days'][$row['day']] = $row['status'];
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Head content stays the same -->
</head>
<body>
<?php include '../sidebar/admin_sidebar.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>
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
          <div class="card-header bg-gradient-success text-white">
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
                  <?php
                  $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                  foreach ($schedules as $info): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($info['vehicle_name']) ?></strong></td>
                      <td><?= htmlspecialchars($info['vehicle_type']) ?></td>
                      <td><?= htmlspecialchars($info['vehicle_capacity']) ?></td>
                      <td><?= htmlspecialchars($info['barangay']) ?></td>
                      <?php foreach ($weekdays as $day): ?>
                        <td><?= !empty($info['days'][$day]) ? htmlspecialchars($info['days'][$day]) : 'Vacant' ?></td>
                      <?php endforeach; ?>
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

<?php include '../includes/footer.php'; ?>
<!-- Scripts remain the same -->
</body>
</html>
