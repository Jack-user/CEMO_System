  <?php
session_start();
include '../includes/header.php'; // Includes the head section and styles

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
  // Redirect to the login page if not logged in
    header("Location: ../index.php");
  exit();
}

// Database connection
include '../includes/conn.php'; // Ensure this file contains your database connection logic

// Fetch vehicle data from the database
$query = "SELECT * FROM waste_service_table";
$result = $conn->query($query);
// Fetch Waste Collection Daily Schedule
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
// Fetch vehicle list with driver names
$sql = "SELECT v.*, 
                CONCAT(d.first_name, ' ', d.last_name) AS driver_name,
                CONCAT(m.m_date, ' ', m.m_time) AS date_time,
                CONCAT('Start Point: ', r.start_point, ' - End Point: ', r.end_point) AS route_info
            FROM waste_service_table v
            LEFT JOIN route_table r ON v.route_id = r.route_id
            LEFT JOIN maintenance_table m ON v.waste_service_id = m.waste_service_id
            LEFT JOIN driver_table d ON v.driver_id = d.driver_id";


$result = $conn->query($sql);

$page_title = "Vehicle List Management"; // Set the page title dynamically
?>
</head>
<body>
  <!-- Sidebar -->
  <?php include '../sidebar/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

      <h1 class="h3 mb-4 text-gray-800"></h1>
      <div class="dropdown d-flex justify-content-end mt-0 mb-0" style="max-width: 120px;">
        <button class="btn btn-success fw-bold shadow-sm dropdown-toggle w-90" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          📅 View
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item" href="vehicle_assignment.php">Vehicle Assignment</a></li>
          <li><a class="dropdown-item" href="waste_service_sched.php">Waste Callendar</a></li>
          <!-- <li><a class="dropdown-item" href="#.php">Vehicle Management</a></li> -->
        </ul>
      </div>
    </div>
    <!-- Page Content -->
<div class="container-fluid py-4">
<div class="d-flex justify-content-start mt-0 mb-0"></div> <!-- You can put a button inside this later -->
  <div class="row">
    <div class="col-12">
      <div class="card shadow-lg">
        <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
          <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
            <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Vehicle List</h5>
          </div>
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
                        <p class="text-xs text-secondary mb-0">
                          <?= !empty($row['date_time']) ? htmlspecialchars($row['date_time']) : 'N/A'; ?>
                        </p>
                      </td>
                      <td class="align-middle text-sm">
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($row['route_info']); ?></p>
                      </td>
                      <td class="align-middle text-center">
                        <a href="#" 
                          class="btn btn-link text-info px-2 py-1 edit-btn" 
                          data-bs-toggle="modal" 
                          data-bs-target="#editVehicleModal"
                          data-id="<?= $row['waste_service_id']; ?>"
                          data-name="<?= htmlspecialchars($row['vehicle_name']); ?>"
                          data-plate="<?= htmlspecialchars($row['plate_no']); ?>"
                          data-capacity="<?= htmlspecialchars($row['vehicle_capacity']); ?>">
                          <i class="material-symbols-rounded fs-5">edit</i>
                        </a>
                        <a href="#"
                          class="btn btn-link text-danger px-2 py-1 delete-btn"
                          data-id="<?= $row['waste_service_id']; ?>"
                          data-name="<?= htmlspecialchars($row['vehicle_name']); ?>"
                          title="Delete">
                          <i class="material-symbols-rounded fs-5">delete</i>
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
          </div> <!-- .table-responsive -->
        </div> <!-- .card-body -->
      </div> <!-- .card -->
    </div> <!-- .col-12 -->
  </div> <!-- .row -->
</div> <!-- .container-fluid -->


    <!-- Edit Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_vehicle.php">
      <div class="modal-content">
        <div class="modal-header bg-gradient-success text-white">
          <h5 class="modal-title" id="editVehicleModalLabel">Edit Vehicle</h5>
          <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="vehicle_id" id="editVehicleId">
          <div class="mb-3">
            <label for="editVehicleName" class="form-label">Vehicle Name</label>
            <input type="text" class="form-control" name="vehicle_name" id="editVehicleName" required>
          </div>
          <div class="mb-3">
            <label for="editPlate" class="form-label">Plate Number</label>
            <input type="text" class="form-control" name="plate_no" id="editPlate" required>
          </div>
          <div class="mb-3">
            <label for="editCapacity" class="form-label">Truck Capacity</label>
            <input type="text" class="form-control" name="vehicle_capacity" id="editCapacity" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Update Vehicle</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="mt-0 mb-0" style="max-width: 170px; margin-left: 15px;">
  <button class="btn btn-success fw-bold shadow-sm w-100" onclick="toggleWasteList(this)">
    🗑️ Collection List
  </button>
</div>

<!-- 🗑️ Waste Collection Schedule Section (Initially Hidden) -->
<div id="WasteListCollection" class="d-none py-3 px-2">
  <div class="row">
    <div class="col-12 mb-2">
      <div class="card shadow-lg">
        <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
          <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
            <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">WASTE COLLECTION DAILY SCHEDULE</h5>
          </div>
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
                  <th>Actions</th>
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
                    <td class="align-middle text-center">
                      <a href="#" 
                        class="btn btn-link text-info px-2 py-1 edit-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editScheduleModal"
                        data-vehicle="<?= htmlspecialchars($vehicle) ?>" 
                        data-day="Monday"
                        data-status="<?= htmlspecialchars($days['Monday'] ?? '') ?>">
                        <i class="material-symbols-rounded fs-5">edit</i>
                      </a>
                      <a href="#" 
                        class="btn btn-link text-danger px-2 py-1 delete-client" 
                        data-id="<?= htmlspecialchars($vehicle) ?>" 
                        title="Delete">
                        <i class="material-symbols-rounded fs-5">delete</i>
                      </a>
                    </td>
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

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="editScheduleForm" action="edit_schedule.php" method="POST">
      <input type="hidden" name="original_vehicle" id="edit_original_vehicle">
      <input type="hidden" name="original_day" id="edit_original_day">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
          <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="vehicle_name" class="form-label">Vehicle</label>
              <input type="text" class="form-control" name="vehicle_name" id="edit_vehicle" required>
            </div>
            <div class="col-md-6">
              <label for="day" class="form-label">Day</label>
              <select class="form-select" name="day" id="edit_day" required>
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                  <option value="<?= $day ?>"><?= $day ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-12">
              <label for="status" class="form-label">Status</label>
              <input type="text" class="form-control" name="status" id="edit_status" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
  </main> 

  <script>
    // ✅ Toggle Waste Collec list visibility
  function toggleWasteList(btn) {
    const section = document.getElementById('WasteListCollection');
    section.classList.toggle('d-none');
    btn.innerHTML = section.classList.contains('d-none') 
      ? '🗑️ Collection List' 
      : '🗑️ Collection List';
  }
  document.querySelectorAll('.delete-client').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.dataset.id;
      const url = this.getAttribute('href');

      Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the schedule permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    });
  });
  
  document.querySelectorAll('.delete-btn').forEach(button => {
  button.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent the link from navigating

    const vehicleId = this.dataset.id;
    const vehicleName = this.dataset.name;

    Swal.fire({
      title: `Delete "${vehicleName}"?`,
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        // Redirect to delete script
        window.location.href = `delete_vehicle.php?id=${vehicleId}`;
      }
    });
  });
});
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
  }
  </script>
</body>
</html>
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

