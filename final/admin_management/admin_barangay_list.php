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
$query = "SELECT brgy_id, barangay  FROM barangays_table"; // Adjust the query based on your table structure
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Hardcoded kapitan and Facebook link data (keyed by barangay name)
$barangayDetails = [
  'Pacol' => [
      'kapitan' => 'Si Jack',
      'facebook_link' => 'https://www.facebook.com/SKPacol',
      'link_text' => 'SK Brgy. Pacol'
  ],
  'Malingin' => [
      'kapitan' => 'Si Joshua',
      'facebook_link' => 'https://www.facebook.com/pages/Brgy%20Malingin,%20Bago%20City/1605809592999113',
      'link_text' => 'Brgy Malingin, Bago City'
  ],
  // Add more barangays here as needed
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Barangay List</title>
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
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Kapitan</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Facebook Link</th>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <?php
                  $barangayName = $row['barangay'];
                  $details = $barangayDetails[$barangayName] ?? null; // Get details for the barangay
                  ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center px-3 py-2">
                        <div>
                          <img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="<?= htmlspecialchars($barangayName); ?>">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm"><?= htmlspecialchars($barangayName); ?></h6>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle text-center text-sm">
                      <?= $details ? htmlspecialchars($details['kapitan']) : '<span class="text-xs text-secondary">N/A</span>'; ?>
                    </td>
                    <td class="align-middle text-center text-sm">
                      <?php if ($details): ?>
                        <a href="<?= htmlspecialchars($details['facebook_link']); ?>" target="_blank" class="text-primary text-xs font-weight-bold" style="text-decoration: none;">
                          <?= htmlspecialchars($details['link_text']); ?>
                        </a>
                      <?php else: ?>
                        <span class="text-xs text-secondary">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-middle text-center">
                      <a href="barangay_edit.php?brgy_id=<?= htmlspecialchars($row['brgy_id']); ?>" class="btn btn-link text-info px-2 py-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Barangay">
                        <i class="material-symbols-rounded fs-5">edit</i>
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
  <!-- Github Buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Material Dashboard JS -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>