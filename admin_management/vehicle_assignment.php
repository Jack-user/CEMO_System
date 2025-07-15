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

// 🔄 Update vehicle info
if (isset($_POST['update_vehicle'])) {
    $vehicleName = $_POST['vehicle_name'];
    $vehicleType = $_POST['vehicle_type'];
    $vehicleCapacity = $_POST['vehicle_capacity'];
    $barangay = $_POST['barangay'];

    $stmt = $conn->prepare("UPDATE waste_service_table SET vehicle_capacity = ? WHERE vehicle_name = ?");
    if (!$stmt) die("Prepare failed (update capacity): " . $conn->error);
    $maintenanceDate = $_POST['maintenance_date'];

    // --- Update vehicle capacity ---
    $stmt = $conn->prepare("UPDATE waste_service_table SET vehicle_capacity = ? WHERE vehicle_name = ?");
    if (!$stmt) {
        die("Prepare failed (update capacity): " . $conn->error);
    }
    $stmt->bind_param("ss", $vehicleCapacity, $vehicleName);
    $stmt->execute();
    $stmt->close();

    // --- Update vehicle type ---
    $stmt = $conn->prepare("UPDATE service_assignment_table s
                            JOIN waste_service_table v ON s.waste_service_id = v.waste_service_id
                            SET s.vehicle_type = ?
                            WHERE v.vehicle_name = ?");
    if (!$stmt) die("Prepare failed (update type): " . $conn->error);
    if (!$stmt) {
        die("Prepare failed (update type): " . $conn->error);
    }
    $stmt->bind_param("ss", $vehicleType, $vehicleName);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT brgy_id FROM barangays_table WHERE barangay = ?");
    if (!$stmt) die("Prepare failed (select brgy_id): " . $conn->error);
    // --- Update barangay assignment ---
    $stmt = $conn->prepare("SELECT brgy_id FROM barangays_table WHERE barangay = ?");
    if (!$stmt) {
        die("Prepare failed (select brgy_id): " . $conn->error);
    }
    $stmt->bind_param("s", $barangay);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $brgyId = $row['brgy_id'];
        $stmt = $conn->prepare("UPDATE service_assignment_table s
                                JOIN waste_service_table v ON s.waste_service_id = v.waste_service_id
                                SET s.brgy_id = ?
                                WHERE v.vehicle_name = ?");
        if (!$stmt) die("Prepare failed (update brgy): " . $conn->error);
        if (!$stmt) {
            die("Prepare failed (update brgy): " . $conn->error);
        }
        $stmt->bind_param("is", $brgyId, $vehicleName);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Invalid barangay input.";
        header("Location: vehicle_assignment.php");
        exit();
    }

    // --- Update maintenance date if provided ---
    if (!empty($maintenanceDate)) {
        $stmt = $conn->prepare("UPDATE maintenance_table m
                                JOIN waste_service_table v ON m.waste_service_id = v.waste_service_id
                                SET m.date_time = ?
                                WHERE v.vehicle_name = ?");
        if (!$stmt) {
            die("Prepare failed (update maintenance): " . $conn->error);
        }
        $stmt->bind_param("ss", $maintenanceDate, $vehicleName);
        $stmt->execute();
        $stmt->close();
    }

    // --- Success message ---
    $_SESSION['success_message'] = "Vehicle details updated successfully.";
    header("Location: vehicle_assignment.php");
    exit();
}
// 🗑️ Delete vehicle assignment
if (isset($_GET['delete'])) {
    $vehicleNameToDelete = $_GET['delete'];

    // Delete from service_assignment_table first
    $stmt = $conn->prepare("DELETE s FROM service_assignment_table s JOIN waste_service_table v ON s.waste_service_id = v.waste_service_id WHERE v.vehicle_name = ?");
    $stmt->bind_param("s", $vehicleNameToDelete);
    $stmt->execute();
    $stmt->close();

    // Then delete from waste_service_table
    $stmt = $conn->prepare("DELETE FROM waste_service_table WHERE vehicle_name = ?");
    $stmt->bind_param("s", $vehicleNameToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Vehicle deleted successfully.";
    header("Location: vehicle_assignment.php");
    exit();
}

?>




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

  <?php if (!empty($_SESSION['success_message']) || !empty($_SESSION['error_message'])): ?>
  <div id="<?= !empty($_SESSION['success_message']) ? 'success-alert' : 'error-alert'; ?>"
      class="alert <?= !empty($_SESSION['success_message']) ? 'alert-success' : 'alert-danger'; ?> shadow text-center"
      role="alert"
      style="
        position: fixed;
        top: 80px;
        left: 400px;
        right: 85px;
        z-index: 1055;
        opacity: 0;
        transition: opacity 0.5s ease;
      ">
    <?= htmlspecialchars(!empty($_SESSION['success_message']) ? $_SESSION['success_message'] : $_SESSION['error_message']); ?>


<?php if (!empty($_SESSION['success_message']) || !empty($_SESSION['error_message'])): ?>
<div id="<?= !empty($_SESSION['success_message']) ? 'success-alert' : 'error-alert'; ?>"
     class="alert <?= !empty($_SESSION['success_message']) ? 'alert-success' : 'alert-danger'; ?> shadow text-center"
     role="alert"
     style="
       position: fixed;
       top: 80px;
       left: 400px;
       right: 85px;
       z-index: 1055;
       opacity: 0;
       transition: opacity 0.5s ease;
     ">
  <?= htmlspecialchars(!empty($_SESSION['success_message']) ? $_SESSION['success_message'] : $_SESSION['error_message']); ?>
</div>
<?php
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<?php endif; ?>



  <div class="container-fluid py-4">
    <div class="d-flex justify-content-start mt-2 mb-4">
      <div class="dropdown">
        <button class="btn btn-success btn-lg fw-bold shadow-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          📅 View 
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item" href="vehicle_assignment.php">Vehicle Assignment</a></li>
          <li><a class="dropdown-item" href="waste_service_sched.php">Waste Collection Schedule</a></li>
          <li><a class="dropdown-item" href="#.php">Vehicle Management</a></li>
        </ul>
      </div>
    </div>

    <div class="row">

    
      <!-- 🚛 Vehicle Assignment Card -->   
          <div class="col-12 mb-4">
  <div class="card shadow-lg">
    <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
      <div style="background: linear-gradient(60deg, #2196f3, #1976d2);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
        <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Vehicle Assignments</h5>
      </div>
    </div>
    <div class="card-body px-0 pb-2">

  <?php if (!empty($_SESSION['success_message'])): ?>
    <div id="success-alert"
         class="alert alert-success text-center mx-auto my-3"
         role="alert"
         style="opacity: 0; transition: opacity 0.5s ease; max-width: 700px;">
      <?= htmlspecialchars($_SESSION['success_message']); ?>
    </div>
  <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <div class="table-responsive p-0">
    <table class="table align-items-center mb-0">

          <thead>
            <tr>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Vehicle</th>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Vehicle Type</th>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Capacity</th>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Area</th>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Maintenance</th>
              <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schedules as $key => $info): ?>
              <tr>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-0 text-sm"><?= htmlspecialchars($info['vehicle_name']) ?></h6>
                    </div>
                  </div>
                </td>
                <td class="align-middle text-sm"><?= htmlspecialchars($info['vehicle_type']) ?></td>
                <td class="align-middle text-sm"><?= htmlspecialchars($info['vehicle_capacity']) ?></td>
                <td class="align-middle text-sm"><?= htmlspecialchars($info['barangay']) ?></td>
                <td class="align-middle text-sm">
                  <?php if (!empty($info['date_time'])): ?>
                    <?= htmlspecialchars($info['date_time']) ?>
                  <?php else: ?>
                    <span class="text-xs text-secondary">N/A</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-center">
  <button type="button" 
          class="btn btn-link text-info px-2 py-1"
          data-bs-toggle="modal"
          data-bs-target="#editModal<?= md5($key); ?>">
    <i class="material-symbols-rounded fs-5">edit</i>
  </button>

  <!-- Edit Vehicle Modal --> 
  <div class="modal fade" 
       id="editModal<?= md5($key); ?>" 
       tabindex="-1" 
       aria-labelledby="editModalLabel<?= md5($key); ?>" 
       aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel<?= md5($key); ?>">Edit Vehicle Assignment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="">
            <input type="hidden" name="vehicle_name" value="<?= htmlspecialchars($info['vehicle_name']); ?>">

            <div class="mb-3">
              <label for="vehicle_type_<?= md5($key); ?>" class="form-label">Vehicle Type</label>
              <input type="text" class="form-control" id="vehicle_type_<?= md5($key); ?>" name="vehicle_type" value="<?= htmlspecialchars($info['vehicle_type']); ?>" required>
            </div>

            <div class="mb-3">
              <label for="vehicle_capacity_<?= md5($key); ?>" class="form-label">Capacity</label>
              <input type="text" class="form-control" id="vehicle_capacity_<?= md5($key); ?>" name="vehicle_capacity" value="<?= htmlspecialchars($info['vehicle_capacity']); ?>" required>
            </div>

            <div class="mb-3">
              <label for="barangay_<?= md5($key); ?>" class="form-label">Area</label>
              <input type="text" class="form-control" id="barangay_<?= md5($key); ?>" name="barangay" value="<?= htmlspecialchars($info['barangay']); ?>" required>
            </div>

            <div class="mb-3">
              <label for="maintenance_<?= md5($key); ?>" class="form-label">Maintenance Date</label>
              <input type="text" class="form-control" id="maintenance_<?= md5($key); ?>" name="maintenance_date" value="<?= htmlspecialchars($info['date_time'] ?? ''); ?>">
            </div>

            <button type="submit" name="update_vehicle" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>
    </div> 
  </div>
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
  <?php
  unset($_SESSION['success_message'], $_SESSION['error_message']);
  ?>
  <?php endif; ?>



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
    <div class="container-fluid py-4">
  <div class="d-flex justify-content-start mt-0 mb-0">
    <!-- Optional: Add buttons here -->
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card shadow-lg">
        <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
          <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
            <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Vehicle Assignment</h5>
          </div>
        </div>

        <div class="card-body px-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Vehicle Name</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Vehicle Type</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Capacity</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Area</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($schedules as $key => $info): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center px-3 py-2">
                        <div>
                          <img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="vehicle">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm"><?= htmlspecialchars($info['vehicle_name']) ?></h6>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle text-sm">
                      <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($info['vehicle_type']) ?></p>
                    </td>
                    <td class="align-middle text-sm">
                      <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($info['vehicle_capacity']) ?></p>
                    </td>
                    <td class="align-middle text-sm">
                      <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($info['barangay']) ?></p>
                    </td>
                    <td class="align-middle text-center">
                      <a href="#" 
                        class="btn btn-link text-info px-2 py-1" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editModal<?= md5($key); ?>">
                        <i class="material-symbols-rounded fs-5">edit</i>
                      </a>
                      <a href="?delete=<?= urlencode($info['vehicle_name']) ?>"
                        class="btn btn-link text-danger px-2 py-1 delete-btn"
                        data-name="<?= htmlspecialchars($info['vehicle_name']) ?>">
                        <i class="material-symbols-rounded fs-5">delete</i>
                      </a>



                      <!-- Edit Modal -->
                      <div class="modal fade" id="editModal<?= md5($key); ?>" tabindex="-1" aria-labelledby="editModalLabel<?= md5($key); ?>" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit Vehicle Assignment</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <form method="POST">
                                <input type="hidden" name="vehicle_name" value="<?= htmlspecialchars($info['vehicle_name']); ?>">
                                <div class="mb-3">
                                  <label class="form-label">Vehicle Type</label>
                                  <input type="text" class="form-control" name="vehicle_type" value="<?= htmlspecialchars($info['vehicle_type']); ?>" required>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Capacity</label>
                                  <input type="text" class="form-control" name="vehicle_capacity" value="<?= htmlspecialchars($info['vehicle_capacity']); ?>" required>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Barangay</label>
                                  <input type="text" class="form-control" name="barangay" value="<?= htmlspecialchars($info['barangay']); ?>" required>
                                </div>
                                <button type="submit" name="update_vehicle" class="btn btn-primary">Save Changes</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div> <!-- .table-responsive -->
        </div> <!-- .card-body -->
      </div> <!-- .card -->
    </div> <!-- .col-12 -->
  </div> <!-- .row -->
</div> <!-- .container-fluid -->

  <?php include '../includes/footer.php'; ?>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
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
});
</script>

</body>
</html>