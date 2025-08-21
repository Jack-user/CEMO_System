<?php
session_start();
include '../includes/conn.php';
include '../includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
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
$page_title = "Client List Management"; // Set the page title dynamically
?>

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
</body>
</html>

<!-- Add this style at the end of your file -->
<style>
        #loader-overlay {
          backdrop-filter: blur(2px);
        }
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
