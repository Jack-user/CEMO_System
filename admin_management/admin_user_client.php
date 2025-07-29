<?php
session_start();
include '../includes/conn.php';
include '../includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$query = "SELECT client_id, first_name, last_name, email, contact, barangay FROM client_table";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM client_table WHERE client_id = '$delete_id'");
    echo '<script>
        Swal.fire({
            icon: "success",
            title: "Deleted!",
            text: "Client deleted successfully.",
            confirmButtonColor: "#66c05eff"
        }).then(() => {
            window.location.href = "../admin_management/admin_user_client.php";
        });
    </script>';
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
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="g-sidenav-show bg-gray-200">
<?php include '../sidebar/admin_sidebar.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card shadow-lg">
          <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
            <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
              <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">Client List</h5>
            </div>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Name</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Email</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Contact</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3">Barangay</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-3 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
<?php 
$editModals = '';
while ($row = mysqli_fetch_assoc($result)):
?>
<tr>
  <td>
    <div class="d-flex align-items-center px-3 py-2">
      <div><img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="<?= htmlspecialchars($row['first_name']); ?>"></div>
      <div class="d-flex flex-column justify-content-center">
        <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></h6>
        <p class="text-xs text-secondary mb-0">Client Email: <?= htmlspecialchars($row['email']); ?></p>
      </div>
    </div>
  </td>
  <td class="align-middle text-sm"><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($row['email']); ?></p></td>
  <td class="align-middle text-sm"><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($row['contact']); ?></p></td>
  <td class="align-middle text-sm"><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($row['barangay']); ?></p></td>
  <td class="align-middle text-center">
    <a href="javascript:void(0);" class="btn btn-link text-info px-2 py-1" data-bs-toggle="modal" data-bs-target="#editClientModal<?= $row['client_id'] ?>" title="Edit Client">
      <i class="material-symbols-rounded fs-5">edit</i>
    </a>
    <a href="?delete_id=<?= $row['client_id']; ?>" class="btn btn-link text-danger px-2 py-1 delete-client" data-id="<?= $row['client_id']; ?>" title="Delete">
      <i class="material-symbols-rounded fs-5">delete</i>
    </a>
  </td>
</tr>
<?php
          $editModals .= '<div class="modal fade" id="editClientModal'.$row['client_id'].'" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered"><form method="POST" action="../backend/admin_edit_client.php"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Edit Client: '.htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']).'</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
          <input type="hidden" name="client_id" value="'.$row['client_id'].'"><div class="row g-3">
          <div class="col-md-6"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" value="'.htmlspecialchars($row['first_name']).'" required></div>
          <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="'.htmlspecialchars($row['last_name']).'" required></div>
          <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="'.htmlspecialchars($row['email']).'" required></div><div class="col-md-6"><label class="form-label">Contact</label><input type="text" class="form-control" name="contact" value="'.htmlspecialchars($row['contact']).'" required></div>
          <div class="col-md-6"><label class="form-label">Barangay</label><input type="text" class="form-control" name="barangay" value="'.htmlspecialchars($row['barangay']).'" readonly></div>
          <div class="col-md-6"><label class="form-label">Password (leave blank to keep current)</label><input type="password" class="form-control" name="password"></div></div></div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></div></form></div></div>';
          endwhile;
          ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?= $editModals; ?>
    <div id="loader-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1051; display: none; align-items: center; justify-content: center;">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"><span class="visually-hidden">Loading...</span></div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <?php include '../backend/admin_edit_client.php'; ?>
    <?php include '../modals/admin_client_edit_modal.php'; ?>
</main>
</body>
</html>

  <!-- Core JS Files -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('input[name="email"], input[name="contact"]').forEach(input => {
      input.addEventListener('blur', function () {
        const field = this.name;
        const value = this.value;
        const clientId = this.closest('form').querySelector('input[name="client_id"]').value;

        fetch(`check_duplicate.php?field=${field}&value=${value}&client_id=${clientId}`)
          .then(res => res.text())
          .then(message => {
            if (message !== '') {
              Swal.fire({
                icon: 'warning',
                title: 'Duplicate Found',
                text: message
              });
              this.focus();
            }
          });
      });
    });

    document.querySelectorAll('.delete-client').forEach(button => {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        const clientId = this.getAttribute('data-id');
        const href = this.getAttribute('href');

        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = href;
          }
        });
      });
    });

    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

        // Show loader on form submission>
    document.querySelectorAll('form[action="../backend/admin_edit_client.php"]').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        const loader = document.getElementById('loader-overlay');
        const formData = new FormData(this);
        
        loader.style.display = 'flex'; // Show loader

        fetch('../backend/admin_edit_client.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          loader.style.display = 'none'; // Hide loader

          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Updated Successfully',
              text: 'Client info has been updated.',
              timer: 2000,
              showConfirmButton: false
            }).then(() => location.reload());
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Update Failed',
              text: data.message
            });
          }
        }).catch(() => {
          loader.style.display = 'none';
          Swal.fire({
            icon: 'error',
            title: 'Request Failed',
            text: 'Something went wrong during update.'
          });
        });
      });
    });
  </script>
  <!-- Github Buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Material Dashboard JS -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>
</html>

<!-- Add this style at the end of your file -->
<style>
#loader-overlay {
  backdrop-filter: blur(2px);
}
</style>
