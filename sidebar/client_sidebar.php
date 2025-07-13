<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get current page for active state
?>

<aside
  class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 my-2"
  id="sidenav-main"
  style="background-color: #1c2e4a;"
>
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-light opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="#">
            <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
            <span class="ms-1 text-sm text-light">Creative Tim</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active bg-gradient-dark text-white' : 'text-light'; ?>" 
                href="../dashboard_management/client_dashboard.php">
                    <i class="material-symbols-rounded opacity-5">dashboard</i>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-light font-weight-bolder opacity-5">Account pages</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'client_profile.php') ? 'active bg-gradient-dark text-white' : 'text-light'; ?>" href="..//client_management/client_profile.php">
                    <i class="material-symbols-rounded opacity-5">person</i>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'sign-in.php') ? 'active bg-gradient-dark text-white' : 'text-light'; ?>"
                href="../login_page/sign-in.php">
                    <i class="material-symbols-rounded opacity-5">login</i>
                    <span class="nav-link-text ms-1">Sign In</span>
                </a>
            </li> -->
            <li class="nav-item">
              <a class="nav-link text-light" href="javascript:void(0);" onclick="showLogoutToast();">
                <i class="material-symbols-rounded opacity-5">logout</i>
                  <span class="nav-link-text ms-1">Logout</span>
              </a>
            </li>

                <!-- Toast Container (Centered at Top) -->
            <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1050;">
              <div id="logoutToast" class="toast text-bg-light border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                  <strong class="me-auto">Confirm Logout</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              <div class="toast-body text-center"> Are you sure you want to log out?
              <div class="d-flex justify-content-center gap-9 mt-4 pt-4 border-top">
                <button type="button" class="btn btn-danger btn-sm" onclick="logoutUser();">Yes, Logout</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancel</button>
              </div>

        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function showLogoutToast() {
    let toastElement = document.getElementById('logoutToast');
    let toast = new bootstrap.Toast(toastElement);
    toast.show();
}

function logoutUser() {
    window.location.href = "../login_page/logout.php"; // Redirect to logout script
}

</script>

<!-- Add this in your <head> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



        </ul>
    </div>
</aside>
