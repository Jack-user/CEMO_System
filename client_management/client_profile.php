<?php
session_start();
require_once '../includes/conn.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

$page_title = "Your Profile";
include '../includes/header.php';

$client_id = $_SESSION['client_id'];

// ✅ Fetch client info safely
$user = null;
$query = $conn->query("SELECT * FROM client_table WHERE client_id = $client_id");

if ($query && $query->num_rows > 0) {
    $user = $query->fetch_assoc();
} else {
    echo "<script>alert('Client not found.'); window.location.href='../login_page/sign-in.php';</script>";
    exit();
}

// ✅ Fetch barangay list
$barangayOptions = '';
$barangayQuery = $conn->query("SELECT barangay FROM barangays_table");
while ($row = $barangayQuery->fetch_assoc()) {
    $selected = ($row['barangay'] == $user['barangay']) ? 'selected' : '';
    $barangayOptions .= "<option value='" . htmlspecialchars($row['barangay']) . "' $selected>" . htmlspecialchars($row['barangay']) . "</option>";
}

$profile_image = '../assets/img/logo.png';
?>
<body class="g-sidenav-show bg-gray-200">
<?php include '../sidebar/client_sidebar.php'; ?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>

  <div class="container py-4">
    <?php if (isset($_SESSION['msg'])): ?>
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '<?php echo $_SESSION["msg"]; ?>',
          confirmButtonColor: '#3085d6'
        });
      </script>
      <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>


<div class="card shadow-sm bg-white p-4 mb-4">
  <div class="row justify-content-center align-items-start g-4">

    <!-- ✅ Profile Image Section -->
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-4 h-100">
        <img src="<?php echo htmlspecialchars($profile_image); ?>"
             alt="Profile Picture"
             class="rounded-circle mx-auto"
             style="width: 180px; height: 180px; object-fit: cover;">

        <form method="post" enctype="multipart/form-data" action="../profile_management/upload_photo.php" class="mt-3">
          <input type="file" name="profile_photo" accept="image/*" class="form-control mb-2" required>
          <button type="submit" class="btn btn-outline-success w-100">Upload</button>
        </form>
      </div>
    </div>

    <!-- ✅ Profile Info Section -->
    <div class="col-md-6">
      <div class="card shadow-sm p-4 h-100">
        <h4 class="fw-bold mb-3">
          <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
        </h4>

        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
          </li>
          <li class="list-group-item">
            <strong>Contact:</strong> <?php echo htmlspecialchars($user['contact']); ?>
          </li>
          <li class="list-group-item">
            <strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?>
          </li>
        </ul>

        <div class="mt-4">
          <button id="openEditProfileModal" class="btn btn-primary bg-success w-100">Edit Profile</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Edit Profile Modal (Clean, Bootstrap) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editProfileForm" method="post" action="../profile_management/update_profile.php">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>

            <div class="col-md-6">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="col-md-6">
              <label for="contact" class="form-label">Contact</label>
              <input type="text" name="contact" id="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
            </div>

            <select name="barangay" class="form-select" required>
  <option value="" disabled>Select Barangay</option>
  <?= $barangayOptions ?>
</select>
            <div class="col-md-6">
              <label for="password" class="form-label">New Password (optional)</label>
              <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .card img {
  transition: transform 0.3s ease;
}
.card img:hover {
  transform: scale(1.05);
}
</style>
<script>
  document.getElementById('openEditProfileModal').addEventListener('click', function () {
    var modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
  });

  document.getElementById('editProfileForm').addEventListener('submit', function (e) {
    e.preventDefault();

    Swal.fire({
      title: 'Are you sure?',
      text: "Save your profile changes?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, save it!',
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit();
      }
    });
  });

document.getElementById('editProfileForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = this;
  const formData = new FormData(form);

  fetch('../profile_management/update_profile.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Profile Updated',
        text: data.message
      }).then(() => location.reload());
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: data.message
      });
    }
  })
  .catch(err => {
    Swal.fire('Error', 'Something went wrong.', 'error');
    console.error(err);
  });
});
</script>
<<<<<<< HEAD
</body>

</html>
</html>
=======
 <?php include '../includes/footer.php'; ?>
</main>
</body>
</html>
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42
