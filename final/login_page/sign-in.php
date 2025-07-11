<?php
session_start();
require_once '../includes/conn.php'; // Ensure this file properly initializes `$pdo`

    // Define Toast Messages
    $messages = [
      "empty_fields" => "Please fill in all fields.",
      "invalid_user" => "No account found with this email.",
      "wrong_password" => "Incorrect password.",
      "success" => "Login successful! Redirecting...",
      ];
      
      $status = $_GET['status'] ?? null;
  
?>

<!-- Toast Notifications -->
<?php if ($status && isset($messages[$status])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastMessage" class="toast align-items-center text-white 
            <?= ($status === 'success') ? 'bg-success' : 'bg-danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($messages[$status]) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let toastEl = document.getElementById("toastMessage");
            let toast = new bootstrap.Toast(toastEl, { delay: 3000 }); // Set delay to 3 sec
            toast.show();
        });
    </script>

    <!-- Auto Redirect if Login is Successful -->
    <?php if ($status === 'success'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../dashboard_management/admin_dashboard.php";
            }, 2000);
        </script>
    <?php endif; ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    CEMO - City Environment Management Office
  </title>
  <style>
  .background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background-color: rgba(97, 94, 94, 0.5); /* Darker gray overlay */
    z-index: 1;
  }
</style>
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="bg-gray-200">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        <!-- Navbar -->
        <!-- End Navbar -->
      </div>
    </div>
  </div>
  <main class="main-content mt-0">
  <section>
  <div class="page-header min-vh-100 position-relative" 
      style="background-image: url('../assets/img/illustrations/bg.jpg'); 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat;">
    <div class="background-overlay"></div> <!-- Background Overlay -->



  <div class="container">
    <div class="row">
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            
            <div class="ms-1 col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-5 text-center justify-content-center flex-column position-relative">
              <!-- Box that contains both the Title and Background Image -->
              <div class="position-relative px-4 py-3 rounded d-flex flex-column align-items-center justify-content-center"
                  style="background: rgba(255, 255, 255, 0.51); color: white; width: 400px; max-width: 400px; text-align: center; 
                          border-radius: 10px; padding: 20px;">
          
                  <!-- Title -->
                  <h4 class="font-weight-bolder m-0">
                      City Environment Management Office
                  </h4>
          
                  <!-- Background Image Inside the Box -->
                  <div class="w-100 mt-3 " 
                      style="background-image: url('../assets/img/illustrations/illustration-signup.png'); 
                              background-size: contain; background-repeat: no-repeat; background-position: center; 
                              height: 200px;">
                  </div>
              </div>
          </div>


          <!-- Sign-in Form -->
        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
          <div class="card card-plain">
            <div class="card-header">
              <h4 class="font-weight-bolder">Sign In</h4>
              <p class="mb-0">Enter your email and password to access your account</p>
            <div class="card-body">
              <form role="form" method="POST" action="sign-in-process.php">
              <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control"  name="email" required>
                    </div>
                    <div class="input-group input-group-outline mb-3 position-relative">
    <label class="form-label">Password</label>
    <input type="password" class="form-control pe-5" name="password" id="password-input" required>
    <span class="input-group-text position-absolute toggle-password" 
          style="top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;">
        👁️
    </span>
</div>
                <!-- <div class="form-check form-check-info text-start ps-0">
                  <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                  <label class="form-check-label" for="rememberMe">
                    Remember me
                  </label>
                </div> -->
                <!-- Show "Reset Password?" Link If Login Fails -->
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                <p class="mt-2 text-center">
                  <?php if (isset($_SESSION['login_error'])): ?>Forgot your password?
                  <a href="" class="text-primary text-gradient font-weight-bold" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" >Reset</a>
                <?php endif; ?>
              </p>
            </div>
              <div class="text-center">
                  <button type="submit" class="btn btn-lg bg-gradient-dark btn-lg w-100 mb-0">Sign In</button>
              </div>
              </form>
              <div class="card-footer text-center pt-0 px-lg-2 px-1 mt-2">
                <p class="mb-2 text-sm mx-auto">Don't have an account?
                <a href="" class="text-primary text-gradient font-weight-bold" data-bs-toggle="modal" data-bs-target="#signUpModal">Sign up</a>
                </p>
              </div>
              </div>
            </div>
          </div>
        </div>
          </div>
        </div>
      </div>
    </div>
          </section>
  </main>
  <?php include 'sign-up.php'; ?>
  <?php include 'forgot-password.php'; ?>
  <?php include 'sign-in-process.php'; ?>
  <?php include 'sign-up-process.php'; ?>

  <!-- Toggle Password Visibility Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Toggle password visibility for all password fields
    document.querySelectorAll(".toggle-password").forEach(toggle => {
        toggle.addEventListener("click", function () {
            // Find the associated password input field
            let passwordInput = this.closest(".input-group").querySelector("input[type='password'], input[type='text']");
            
            if (passwordInput) {
                // Toggle the input type between 'password' and 'text'
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    this.innerHTML = "👁️‍🗨️"; // Open eye icon
                } else {
                    passwordInput.type = "password";
                    this.innerHTML = "👁️"; // Closed eye icon
                }
            }
        });
    });
});
</script>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="../assets/css/material-dashboard.css?v=3.2.0"></script>
  

 <!-- Github buttons -->
 <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>
</html>
