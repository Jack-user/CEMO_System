<?php
session_start();
require_once '../includes/conn.php';

// Check if the user is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

$page_title = "Your Profile";
include '../includes/header.php'; // Head and styles 

// Load client data
$client_id = $_SESSION['client_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM client_table WHERE client_id = ?");
$stmt->execute([$client_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Client not found.");
}

$profile_image = '../assets/default-profile.png';
?>
<body class="g-sidenav-show bg-gray-200">
<!-- Sidebar -->
<?php include '../sidebar/client_sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <!-- Navbar -->
  <?php include '../includes/navbar.php'; ?>

  <?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
      <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="container py-4">
    <div class="card mx-auto" style="max-width: 600px; min-height: 500px;">
      <div class="card-body text-center py-5">
        <img src="<?php echo htmlspecialchars($profile_image); ?>"
             alt="Profile Picture"
             class="rounded-circle mb-4"
             style="width: 200px; height: 200px; object-fit: cover;">

        <h2 class="fw-bold mb-2" style="font-size: 2rem;">
          <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
        </h2>

        <p class="text-muted mb-4" style="font-size: 1.1rem;">
          <?php echo htmlspecialchars($user['email']); ?>
        </p>

        <div class="d-grid gap-2 col-6 mx-auto">
          <!-- This button opens the modal -->
          <button id="openEditProfileModal" class="btn btn-primary btn-lg">
            Edit Profile
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- ✅ MODAL HTML -->
<div id="editProfileModal" class="modal">
  <div class="modal-content">
    <span id="closeEditProfileModal" class="close">&times;</span>
    <h2>Edit Profile</h2>
    <form method="post" action="../profile_management/update_profile.php">
      <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

      <label for="first_name">First Name:</label><br>
      <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required><br><br>

      <label for="last_name">Last Name:</label><br>
      <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required><br><br>

      <label for="email">Email:</label><br>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>

<!-- ✅ MODAL STYLES -->
<style>
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  inset: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
}
.modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 6px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  width: 90%;
  max-width: 400px;
  position: relative;
  animation: fadeIn 0.3s ease;
  font: 15px Arial, sans-serif;
  line-height: 1.4;
}
@keyframes fadeIn {
  from {opacity:0;transform:scale(0.95);}
  to {opacity:1;transform:scale(1);}
}
.close {
  position: absolute;
  top: 10px;
  right: 12px;
  font-size: 22px;
  font-weight: bold;
  color: #333;
  cursor: pointer;
}
.close:hover {color:#007bff;}
.modal-content h2 {
  margin:0 0 10px;
  font-size:1.3rem;
  text-align:center;
}
.modal-content form {
  display:flex;
  flex-direction:column;
}
.modal-content label {
  margin-top:10px;
  font-size:0.9rem;
  color:#555;
}
.modal-content input {
  padding:8px;
  margin-top:4px;
  border:1px solid #ccc;
  border-radius:4px;
  font-size:0.95rem;
}
.modal-content button[type="submit"] {
  margin-top:16px;
  padding:10px;
  background:#007bff;
  border:none;
  color:#fff;
  font-size:1rem;
  border-radius:4px;
  cursor:pointer;
}
.modal-content button[type="submit"]:hover {
  background:#0056b3;
}
</style>

<!-- ✅ MODAL SCRIPT -->
<script>
const modal = document.getElementById('editProfileModal');
const openBtn = document.getElementById('openEditProfileModal');
const closeBtn = document.getElementById('closeEditProfileModal');

openBtn.onclick = function () {
  modal.classList.add('show');
};
closeBtn.onclick = function () {
  modal.classList.remove('show');
};
window.onclick = function (event) {
  if (event.target === modal) {
    modal.classList.remove('show');
  }
};
</script>

</body>
</html>
