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

$barangayQuery = mysqli_query($conn, "SELECT barangay FROM barangays_table");

// Fetch staff data from admin_table
$staffQuery = "SELECT admin_id, user_role, first_name, last_name, email, contact, address FROM admin_table";
$staffResult = mysqli_query($conn, $staffQuery);
if (!$staffResult) {
    die("Staff query failed: " . mysqli_error($conn));
}

// Fetch driver data from driver_table
$driverQuery = "SELECT driver_id, first_name, last_name, contact, address, age, gender, license_no FROM driver_table";
$driverResult = mysqli_query($conn, $driverQuery);
if (!$driverResult) {
    die("Driver query failed: " . mysqli_error($conn));
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM admin_table WHERE admin_id = '$delete_id'");
    $_SESSION['success_message'] = "Admin deleted successfully.";
    header("Location: ../admin_management/admin_role_list.php");
    exit();
}
// Handle Driver Delete
if (isset($_GET['delete_driver_id'])) {
    $delete_id = $_GET['delete_driver_id'];
    mysqli_query($conn, "DELETE FROM driver_table WHERE driver_id = '$delete_id'");
    $_SESSION['driver_success'] = "Driver deleted successfully.";
    header("Location: admin_role_list.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Client User List</title>
  <!-- Fonts and Icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>
<body class="g-sidenav-show bg-gray-200">
  <!-- Sidebar -->
  <?php include '../sidebar/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>
    <div class="d-flex justify-content-end mt-0 mb-0" style="max-width: 135px;">
      <button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add New Staff</button>
    </div>
      <!-- Add Staff Modal -->
        <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form method="POST" action="add_staff.php" id="addStaffForm">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Add New Staff</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label>First Name</label>
                      <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                      <label>Last Name</label>
                      <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                      <label>User Role</label>
                      <select name="user_role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Staff">Staff</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label>Gender</label>
                      <select name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label>Email</label>
                      <input type="email" name="email" id="staffEmail" class="form-control" required
                            pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
                            title="Only Gmail addresses are allowed (e.g., example@gmail.com)">
                      <div id="emailFeedback" class="form-text text-danger"></div>
                    </div>
                    <div class="col-md-6">
                      <label>Contact</label>
                      <input type="text" name="contact" id="staffContact" class="form-control" required
                            maxlength="11" minlength="11"
                            pattern="^[0-9]{11}$"
                            title="Contact must be 11 digits only (e.g., 09123456789)">
                      <div id="contactFeedback" class="form-text text-danger"></div>
                    </div>
                    <select name="address" class="form-select" required>
                      <option value="">Select Barangay</option>
                      <?php while ($bgy = mysqli_fetch_assoc($barangayQuery)): ?>
                        <option value="<?= htmlspecialchars($bgy['barangay']); ?>">
                          <?= htmlspecialchars($bgy['barangay']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>

                    <div class="col-md-6">
                      <label>Birth Date</label>
                      <input type="date" name="birth_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                    <label for="editPassword">Password</label>
                    <div class="position-relative">
                      <input type="password" name="password" id="editPassword" class="form-control pe-5" required>
                      <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 border-0 bg-transparent toggle-password" data-target="editPassword" tabindex="-1">
                        👁️‍🗨️
                      </button>
                    </div>
                  </div>


                  </div>
                </div>
                <div class="modal-footer mt-2">
                  <button type="submit" class="btn btn-primary">Save Staff</button>
                </div>
              </div>
            </form>
          </div>
        </div>
                        
        <!-- Edit Staff Modal -->
          <div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <form method="POST" action="update_staff.php" id="editStaffForm">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="admin_id" id="editAdminId">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="editLastName" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label>User Role</label>
                        <select name="user_role" id="editUserRole" class="form-select" required>
                          <option value="Admin">Admin</option>
                          <option value="Staff">Staff</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$">
                        <div id="editEmailFeedback" class="form-text text-danger"></div>
                      </div>
                      <div class="col-md-6">
                        <label>Contact</label>
                        <input type="text" name="contact" id="editContact" class="form-control"  
                        maxlength="11" minlength="11" required pattern="^[0-9]{11}$">
                        <div id="editContactFeedback" class="form-text text-danger"></div>
                      </div>
                      <div class="col-md-6">
                        <label>Barangay</label>
                        <select name="address" id="editAddress" class="form-select" required>
                          <?php
                          $barangayQuery2 = mysqli_query($conn, "SELECT barangay FROM barangays_table");
                          while ($bgy2 = mysqli_fetch_assoc($barangayQuery2)): ?>
                            <option value="<?= htmlspecialchars($bgy2['barangay']); ?>">
                              <?= htmlspecialchars($bgy2['barangay']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                    <label for="editPassword">Password</label>
                    <div class="position-relative">
                      <input type="password" name="password" id="editPassword" class="form-control pe-5">
                      <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 border-0 bg-transparent toggle-password" data-target="editPassword" tabindex="-1">
                        👁️‍🗨️
                      </button>
                    </div>
                        <div class="form-text">Leave blank if you don’t want to change password</div>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer mt-2">
                    <button type="submit" class="btn btn-primary">Update Staff</button>
                  </div>
                </div>
              </form>
            </div>
          </div>


    <!-- Page Content -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card shadow-lg">
            <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
                    <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
                      <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Staff List</h5>
                    </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Name</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Role</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Email</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Contact</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Barangay</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = mysqli_fetch_assoc($staffResult)): ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <img src="../assets/img/logo.png" class="avatar avatar-sm me-3 border-radius-lg" alt="<?= htmlspecialchars($row['first_name']); ?>">
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></h6>
                              <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($row['email']); ?></p>
                            </div>
                          </div>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($row['user_role']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($row['email']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($row['contact']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($row['address']); ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <a href="#" class="btn btn-link text-info px-2 py-1 edit-btn"
                            data-id="<?= $row['admin_id']; ?>"
                            data-fname="<?= htmlspecialchars($row['first_name']); ?>"
                            data-lname="<?= htmlspecialchars($row['last_name']); ?>"
                            data-role="<?= htmlspecialchars($row['user_role']); ?>"
                            data-email="<?= htmlspecialchars($row['email']); ?>"
                            data-contact="<?= htmlspecialchars($row['contact']); ?>"
                            data-address="<?= htmlspecialchars($row['address']); ?>"
                            data-bs-toggle="modal" data-bs-target="#editStaffModal">
                            <i class="material-symbols-rounded fs-5">edit</i>
                          </a>

                          <a href="?delete_id=<?= $row['admin_id']; ?>"
                          class="btn btn-link text-danger px-2 py-1 delete-client"
                          data-id="<?= $row['admin_id']; ?>"
                          title="Delete">
                          <i class="material-symbols-rounded fs-5">delete</i>
                        </a>
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
    <!-- Button to show/hide Driver List -->
      <div class="d-flex justify-content-start mt-2 mb-0" style="max-width: 130px;">
        <button class="btn btn-success fw-bold shadow-sm w-100" onclick="toggleDriverList()">
          👷 Driver List
        </button>
      </div>
    <!-- Driver List Section (hidden by default) -->
      <div id="driverListSection" class="container-fluid py-4" style="display: none;">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-lg">
              <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
                    <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
                  <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Driver List</h5>
                </div>
              </div>
              <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0">
                  <table class="table align-items-center mb-0">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Name</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">License No.</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Age</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Contact</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Barangay</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($driver = mysqli_fetch_assoc($driverResult)): ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <img src="../assets/img/logo.png" class="avatar avatar-sm me-3 border-radius-lg" alt="<?= htmlspecialchars($driver['first_name']); ?>">
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($driver['first_name']) . ' ' . htmlspecialchars($driver['last_name']); ?></h6>
                              <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($driver['gender']); ?></p>
                            </div>
                          </div>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($driver['license_no']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($driver['age']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($driver['contact']); ?></p>
                        </td>
                        <td class="align-middle text-sm">
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($driver['address']); ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <button 
                          class="btn btn-link text-info px-2 py-1 edit-driver-btn" 
                          data-id="<?= htmlspecialchars($driver['driver_id']); ?>" 
                          data-fname="<?= htmlspecialchars($driver['first_name']); ?>"
                          data-lname="<?= htmlspecialchars($driver['last_name']); ?>"
                          data-contact="<?= htmlspecialchars($driver['contact']); ?>"
                          data-address="<?= htmlspecialchars($driver['address']); ?>"
                          data-age="<?= htmlspecialchars($driver['age']); ?>"
                          data-gender="<?= htmlspecialchars($driver['gender']); ?>"
                          data-license="<?= htmlspecialchars($driver['license_no']); ?>"
                          data-bs-toggle="modal" 
                          data-bs-target="#editDriverModal"
                          title="Edit Driver"
                        >
                            <i class="material-symbols-rounded fs-5">edit</i>
                          </button>

                          <a href="?delete_driver_id=<?= $driver['driver_id']; ?>" class="btn btn-link text-danger px-2 py-1 delete-driver" data-id="<?= $driver['driver_id']; ?>">
                            <i class="material-symbols-rounded fs-5">delete</i>
                          </a>

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
    </div>
    <!-- Edit Driver Modal -->
    <div class="modal fade" id="editDriverModal" tabindex="-1" aria-labelledby="editDriverModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form method="POST" action="update_driver.php" id="editDriverForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Driver</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="driver_id" id="editDriverId">
              <div class="row g-3">
                <div class="col-md-6">
                  <label>First Name</label>
                  <input type="text" name="first_name" id="editDriverFirstName" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label>Last Name</label>
                  <input type="text" name="last_name" id="editDriverLastName" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label>Contact</label>
                  <input type="text" name="contact" id="editDriverContact" class="form-control"
                  maxlength="11" minlength="11" required pattern="^[0-9]{11}$">
                </div>
                <div class="col-md-6">
                  <label>Address</label>
                  <select name="address" id="editDriverAddress" class="form-select" required>
                    <?php
                    $barangayQuery2 = mysqli_query($conn, "SELECT barangay FROM barangays_table");
                    while ($bgy2 = mysqli_fetch_assoc($barangayQuery2)): ?>
                      <option value="<?= htmlspecialchars($bgy2['barangay']); ?>">
                        <?= htmlspecialchars($bgy2['barangay']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Gender</label>
                  <select name="gender" id="editDriverGender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label>Age</label>
                  <input type="number" name="age" id="editDriverAge" class="form-control" required min="18" max="100">
                </div>
                <!-- <div class="col-md-6">
                  <label>Birth Date</label>
                  <input type="date" name="birth_date" id="editDriverBirthDate" class="form-control" required>
                </div> -->
                <div class="col-md-6">
                  <label for="editDriverLicenseNo">License No.</label>
                  <div class="input-group">
                    <input type="hidden" name="driver_id" id="editDriverId">
                    <input type="text" name="license_no" id="editDriverLicenseNo" class="form-control" required>
                    <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 border-0 bg-transparent toggle-password" data-target="editDriverLicenseNo">👁️‍🗨️</button>
                  </div>
                </div>
                
                <div class="col-md-6">
                    <label for="editPassword">Password</label>
                    <div class="position-relative">
                      <input type="password" name="password" id="editPassword" class="form-control pe-5">
                      <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 border-0 bg-transparent toggle-password" data-target="editPassword" tabindex="-1">
                        👁️‍🗨️
                      </button>
                    </div>
                  <div class="form-text">Leave blank if you don’t want to change password</div>
                </div>
              </div>
            </div>
            <div class="modal-footer mt-2">
              <button type="submit" class="btn btn-primary">Update Driver</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
  </main>

  <!-- Core JS Files -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-XuSNtNmnH/+gG+/+rbXY8yTGyGwU2UVoWrQz/NTgkM4V4IqKfI6+kJJJEVnEuGbr" crossorigin="anonymous"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <!-- Github Buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Material Dashboard JS -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script>
        // 🧠 Fill the modal when edit button is clicked
          document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
              document.getElementById('editAdminId').value = button.dataset.id;
              document.getElementById('editFirstName').value = button.dataset.fname;
              document.getElementById('editLastName').value = button.dataset.lname;
              document.getElementById('editUserRole').value = button.dataset.role;
              document.getElementById('editEmail').value = button.dataset.email;
              document.getElementById('editContact').value = button.dataset.contact;
              document.getElementById('editAddress').value = button.dataset.address;
            });
          });

          // 👁️ Password toggle👁️‍🗨️ 
          document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
              const targetId = this.dataset.target;
              const input = document.getElementById(targetId);
              const isPassword = input.type === 'password';

              input.type = isPassword ? 'text' : 'password';
              this.textContent = isPassword ? '👁️' : '👁️‍🗨️'; // Toggle icon emoji
            });
          });
        
      const staffEmail = document.getElementById('staffEmail');
      const staffContact = document.getElementById('staffContact');
      const emailFeedback = document.getElementById('emailFeedback');
      const contactFeedback = document.getElementById('contactFeedback');

      // ✅ Check for duplicate email
      staffEmail.addEventListener('input', function () {
        const email = this.value.trim();

        // Allow validation only if input ends with @gmail.com
        if (!email.endsWith("@gmail.com")) {
          emailFeedback.textContent = "Only Gmail addresses are allowed.";
          return;
        }

        fetch(`check_email.php?email=${encodeURIComponent(email)}`)
          .then(response => response.text())
          .then(data => {
            emailFeedback.textContent = data !== "available" ? "Email already exists." : "";
          });
      });

      // ✅ Real-time Gmail domain validation on blur
      staffEmail.addEventListener('blur', function () {
        const email = this.value.trim();
        const pattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        if (!pattern.test(email)) {
          emailFeedback.textContent = "Only gmail@.com addresses are allowed.";
        } else if (emailFeedback.textContent === "Only gmail@.com addresses are allowed.") {
          emailFeedback.textContent = "";
        }
      });

      // ✅ Check for duplicate contact
      staffContact.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Allow only digits

        const contact = this.value;
        if (contact.length > 0 && contact.length <= 11) {
          fetch(`check_contact.php?contact=${encodeURIComponent(contact)}`)
            .then(response => response.text())
            .then(data => {
              contactFeedback.textContent = data !== "available" ? "Contact number already exists." : "";
            });
        }
      });

      // ✅ Length validation for contact (11 digits)
      staffContact.addEventListener('blur', function () {
        const contact = this.value;
        if (contact.length !== 11) {
          contactFeedback.textContent = "Contact must be exactly 11 digits.";
        } else if (contactFeedback.textContent === "Contact must be exactly 11 digits.") {
          contactFeedback.textContent = "";
        }
      });

      // ✅ Toggle driver list visibility
      function toggleDriverList() {
        const section = document.getElementById('driverListSection');
        section.style.display = (section.style.display === 'none') ? 'block' : 'none';
      }

      // ✅ Scrollbar config
      var win = navigator.platform.indexOf('Win') > -1;
      if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = { damping: '0.5' };
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
      }

      // ✅ SweetAlert success (from session)
      <?php if (isset($_SESSION['staff_success'])): ?>
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: '<?= $_SESSION['staff_success']; ?>',
          confirmButtonColor: '#3085d6'
        });
        <?php unset($_SESSION['staff_success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['driver_success'])): ?>
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: '<?= $_SESSION['driver_success']; ?>',
          confirmButtonColor: '#3085d6'
        });
        <?php unset($_SESSION['driver_success']); ?>
        <?php endif; ?>


      // ✅ SweetAlert delete confirmation
      document.querySelectorAll('.delete-client').forEach(button => { 
        button.addEventListener('click', function (e) {
          e.preventDefault();
          const clientId = this.getAttribute('data-id');
          Swal.fire({
            title: 'Delete this Staff?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = `?delete_id=${clientId}`;
            }
          });
        });
      });

      document.querySelectorAll('.delete-driver').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const driverId = this.dataset.id;
      Swal.fire({
        title: 'Delete this driver?',
        text: "You won't be able to undo this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = `?delete_driver_id=${driverId}`;
        }
      });
    });
  });

  document.querySelectorAll('.edit-driver-btn').forEach(button => {
  button.addEventListener('click', () => {
    document.getElementById('editDriverId').value = button.dataset.id;
    document.getElementById('editDriverFirstName').value = button.dataset.fname;
    document.getElementById('editDriverLastName').value = button.dataset.lname;
    document.getElementById('editDriverContact').value = button.dataset.contact;
    document.getElementById('editDriverAddress').value = button.dataset.address;
    document.getElementById('editDriverAge').value = button.dataset.age;
    document.getElementById('editDriverLicenseNo').value = button.dataset.license;
    
    // If you have gender or birth_date fields, handle them too
    if (document.getElementById('editDriverGender')) {
      document.getElementById('editDriverGender').value = button.dataset.gender;
    }
    if (document.getElementById('editDriverBirthDate')) {
      document.getElementById('editDriverBirthDate').value = button.dataset.birthdate || '';
    }
  });
});
</script>

</body>
</html>

<style>
  /* Add this in your <style> section */
  input.is-invalid {
    border-color: red;
  }
  input.is-valid {
    border-color: green;
  }
  .input-group .form-control {
  border-right: 0;
}
.input-group .btn {
  border-left: 0;
}

  </style>
