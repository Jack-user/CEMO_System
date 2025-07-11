<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login_page/sign-in.php");
  exit();
}

// Fetch data from schedule table (assumed structure: vehicle_name, day, status)
$schedules = [];
$sql = "SELECT w.vehicle_name, s.day, s.status 
        FROM schedule_table s 
        LEFT JOIN waste_service_table w ON w.waste_service_id = s.waste_service_id 
        ORDER BY w.vehicle_name";


$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $schedules[$row['vehicle_name']][$row['day']] = $row['status'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Waste Collection Schedule</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
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
              <h5 class="text-center text-uppercase font-weight-bold mb-0">Waste Collection Daily Schedule</h5>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table table-bordered align-items-center mb-0 text-center">
                  <thead class="bg-light">
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Vehicle</th>
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
                    <?php foreach ($schedules as $vehicle => $days): ?>
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
      </div>

         <!-- Toggle Button -->
    <div class="d-flex justify-content-end mt-4 mb-3">
    <button id="toggleEventBtn" class="btn btn-warning btn-lg fw-bold shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#eventsSchedule" aria-expanded="false" aria-controls="eventsSchedule">
      📅 View Maintenance Schedule    </button>
  </div>

  <!-- Upcoming Events Schedule - February -->
  <div class="row mt-2">
    <div class="col-12">
      <!-- Add 'collapse' class to make sure it's hidden initially -->
      <div class="collapse" id="eventsSchedule">
        <div class="card shadow-lg">
          <div class="card-header bg-gradient-warning text-white">
            <h5 class="text-center text-uppercase font-weight-bold mb-0">View Maintenance Schedule</h5>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table table-bordered align-items-center mb-0 text-center">
                <thead class="bg-light">
                  <tr>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Event</th>
                    <th colspan="29" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">February</th>
                  </tr>
                  <tr>
                    <th></th>
                    <?php for ($i = 1; $i <= 29; $i++): ?>
                      <th><?= $i ?></th>
                    <?php endfor; ?>
                  </tr>
                </thead>
                <tbody>
                  <!-- Event Rows -->
                  <tr>
                    <td><strong>Community Clean-up</strong></td>
                    <?php for ($i = 1; $i <= 29; $i++): ?>
                      <td><?= ($i == 5) ? 'Scheduled' : '' ?></td>
                    <?php endfor; ?>
                  </tr>
                  <tr>
                    <td><strong>Waste Collection Awareness</strong></td>
                    <?php for ($i = 1; $i <= 29; $i++): ?>
                      <td><?= ($i == 12) ? 'Scheduled' : '' ?></td>
                    <?php endfor; ?>
                  </tr>
                  <tr>
                    <td><strong>Bago City Fiesta</strong></td>
                    <?php for ($i = 1; $i <= 29; $i++): ?>
                      <td><?= ($i == 19) ? 'Scheduled' : '' ?></td>
                    <?php endfor; ?>
                  </tr>
                  <tr>
                    <td><strong>Environmental Seminar</strong></td>
                    <?php for ($i = 1; $i <= 29; $i++): ?>
                      <td><?= ($i == 25) ? 'Scheduled' : '' ?></td>
                    <?php endfor; ?>
                  </tr>
                  <script>
                      const toggleBtn = document.getElementById('toggleEventBtn');
                      const collapseElement = document.getElementById('eventsSchedule');

                  // Set button text based on the initial state
                  if (collapseElement.classList.contains('show')) {
                      toggleBtn.textContent = "📅 Unview Maintenance Schedule";
                  } else {
                      toggleBtn.textContent = "📅 View Maintenance Schedule";
                  }

                  // When the collapse is hidden, change the button text
                  collapseElement.addEventListener('hidden.bs.collapse', function () {
                      toggleBtn.textContent = "📅 View Maintenance Schedule";
                  });

                  // When the collapse is shown, change the button text
                  collapseElement.addEventListener('shown.bs.collapse', function () {
                      toggleBtn.textContent = "📅 Unview Maintenance Schedule";
                  });
                  </script>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include '../includes/footer.php'; ?>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
    }
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>


 