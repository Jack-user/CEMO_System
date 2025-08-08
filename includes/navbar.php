<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/conn.php';

// Default user info
$first_name = 'User';
$user_role = 'guest';

// Fetch user info based on session type
if (isset($_SESSION['client_id'])) {
    $client_id = $_SESSION['client_id'];
    $stmt = $conn->prepare("SELECT first_name FROM client_table WHERE client_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $stmt->bind_result($first_name);
    $stmt->fetch();
    $stmt->close();
    $user_role = 'Client';
} elseif (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT first_name, user_role FROM admin_table WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $user_role);
    $stmt->fetch();
    $stmt->close();
}
?>

<!-- Navbar -->
 <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="#">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <ul class="navbar-nav d-flex align-items-center justify-content-end">
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
            </li>

                <!-- Settings Dropdown -->
                <li class="nav-item dropdown px-3 d-flex align-items-center">
                    <a href="#" class="nav-link text-body p-0" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown">
                        <li><a class="dropdown-item" href="#"><i class="material-symbols-rounded me-2">tune</i> Preferences</a></li>
                        <li><a class="dropdown-item" href="#"><i class="material-symbols-rounded me-2">help</i> Help</a></li>
                    </ul>
                </li>

                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown pe-3 d-flex align-items-center">
                    <a href="#" class="nav-link text-body p-0" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="material-symbols-rounded">notifications</i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown">
                        <li><a class="dropdown-item" href="#"><i class="material-symbols-rounded me-2">mail</i> New message</a></li>
                        <li><a class="dropdown-item" href="#"><i class="material-symbols-rounded me-2">event</i> Upcoming event</a></li>
                    </ul>
                </li>

                <!-- User Account & Name Dropdown -->
                <li class="nav-item dropdown d-flex align-items-center">
                    <a href="#" class="nav-link dropdown-toggle text-body font-weight-bold px-0 d-flex align-items-center gap-2"
                       id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="material-symbols-rounded fs-5">account_circle</i>
                        <span class="bg-success rounded-circle d-inline-block" style="width: 6px; height: 6px;"></span>
                        <small class="text-black fw-bold">
                            <?= htmlspecialchars($first_name) ?> (<?= ucfirst(htmlspecialchars($user_role)) ?>)
                        </small>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded" aria-labelledby="profileDropdown">
                        <?php if ($user_role === 'Client'): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../client_management/client_profile.php">
                                    <i class="material-symbols-rounded">person</i> <span>Profile</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../admin_management/admin_profile.php">
                                    <i class="material-symbols-rounded">person</i> <span>Profile</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="showLogoutToast(); return false;">
                                <i class="material-symbols-rounded">logout</i> <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
   </div> 
</nav> 

<!-- Toast Container (Centered at Top) -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1050;">
    <div id="logoutToast" class="toast text-bg-light border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Confirm Logout</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body text-center">
            Are you sure you want to log out?
            <div class="d-flex justify-content-center gap-3 mt-4 pt-4 border-top">
                <button type="button" class="btn btn-danger btn-sm" onclick="logoutUser()">Yes, Logout</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function showLogoutToast() {
    let toastElement = document.getElementById('logoutToast');
    let toast = new bootstrap.Toast(toastElement);
    toast.show();
}

function logoutUser() {
    window.location.href = "../login_page/logout.php";
}

document.addEventListener("DOMContentLoaded", function () {
    const sidebarToggler = document.getElementById("iconNavbarSidenav");
    const sidebar = document.getElementById("sidenav-main"); // Works for both admin and client

    if (sidebarToggler && sidebar) {
        sidebarToggler.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent anchor default behavior
            sidebar.classList.toggle("active");
             document.body.classList.toggle("sidebar-open");
        });
    }
});

 document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidenav-main');
    const toggleButton = document.getElementById('iconNavbarSidenav'); // Your hamburger menu button
    const isSidebarOpen = sidebar.classList.contains('active');

    // If sidebar is open and click is outside both sidebar and toggle button
    if (
      isSidebarOpen &&
      !sidebar.contains(event.target) &&
      !toggleButton.contains(event.target)
    ) {
      sidebar.classList.remove('active');
      document.body.classList.remove('sidebar-open');
    }
  });
</script>

