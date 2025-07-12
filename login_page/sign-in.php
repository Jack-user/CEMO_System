<?php
session_start();
require_once '../includes/conn.php';

// Define Toast Messages
$messages = [
  "empty_fields" => "Please fill in all fields.",
  "invalid_user" => "No account found with this email.",
  "wrong_password" => "Incorrect password.",
  "success" => "Login successful! Redirecting...",
];

$status = $_GET['status'] ?? null;
?>

<?php if ($status && isset($messages[$status])): ?>
  <div class="container position-absolute top-0 start-50 translate-middle-x mt-4" style="z-index: 1050; max-width: 500px;">
    <?php if ($status === 'success'): ?>
      <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($messages[$status]) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
      <script>
        setTimeout(() => {
          window.location.href = "../dashboard_management/admin_dashboard.php";
        }, 3000);
      </script>
    <?php else: ?>
      <div class="alert alert-danger alert-dismissible text-white fade show mb-0" role="alert">
        <span class="text-sm">
          <?= htmlspecialchars($messages[$status]) ?>
          <?php if ($status === 'invalid_user'): ?>
            <a href="javascript:;" class="alert-link text-white" data-bs-toggle="modal" data-bs-target="#signUpModal">Sign up</a> if you don’t have an account.
          <?php endif; ?>
        </span>
        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>






    <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            let toastEl = document.getElementById("toastMessage");
            let toast = new bootstrap.Toast(toastEl, { delay: 3000 }); // Set delay to 3 sec
            toast.show();
        });
    </script>

    Auto Redirect if Login is Successful -->
    <!-- <?php if ($status === 'success'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../dashboard_management/admin_dashboard.php";
            }, 2000);
        </script>
    <?php endif; ?> -->

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
                <!-- Email -->
                <div class="card-body">
                  <form role="form" method="POST" action="sign-in-process.php">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control" name="email" id="email-input" 
                            required autocomplete="username"
                            value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                    </div>
                    <!-- Password -->
                      <div class="input-group input-group-outline mb-3 position-relative">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password-input" required autocomplete="current-password">
                        <button type="button" id="togglePassword" class="btn position-absolute " 
                            style="top: 50%; right: 10px; transform: translateY(-50%); z-index: 10; background: none; border: none;">
                          <span id="togglePassword"
                            style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
                              cursor: pointer; font-size: 1.2rem;">👁️
                          </span>
                        </button>
                      </div>
                      <small id="emailError" class="text-danger text-center d-block d-none">Email must end with @gmail.com</small>

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
          </section>
  </main>
  <?php include 'sign-up.php'; ?>
  <?php include 'forgot-password.php'; ?>
  <?php include 'sign-in-process.php'; ?>
  <?php include 'sign-up-process.php'; ?>

  <!-- Toggle Password Visibility & Input Outline Color Script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const emailInput = document.getElementById("email-input");
    const emailGroup = emailInput.closest(".input-group");

    // Check if input has a value on load (e.g., from $_GET['email']) and apply 'is-filled'
    if (emailInput.value.trim() !== "") {
      emailGroup.classList.add("is-filled");
    }

    // Keep updating on user typing
    emailInput.addEventListener("input", () => {
      if (emailInput.value.trim() !== "") {
        emailGroup.classList.add("is-filled");
      } else {
        emailGroup.classList.remove("is-filled");
      }
    });
  });

  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.classList.remove('show');
      alert.classList.add('hide');
    }
  }, 2000); // 2 seconds

  document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password-input");
  const emailInput = document.getElementById("email-input");
  const toggleBtn = document.getElementById("togglePassword");
  const emailError = document.getElementById("emailError");
  const form = document.querySelector("form");

  toggleBtn.addEventListener("click", function () {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    this.textContent = type === "password" ? "👁️" : "👁️‍🗨️";
  });

  // Remove outlines on input
  passwordInput.addEventListener("input", () => {
    passwordInput.classList.remove("is-valid", "is-invalid");
  });

  emailInput.addEventListener("input", () => {
    emailInput.classList.remove("is-valid", "is-invalid");
    emailError.classList.add("d-none");
  });

  // Email domain validation
  form.addEventListener("submit", function (e) {
    const emailValue = emailInput.value.trim();

    if (!emailValue.endsWith("@gmail.com")) {
      e.preventDefault(); // Prevent form from submitting
      emailError.classList.remove("d-none");
      emailInput.classList.add("is-invalid");
    }
  });

document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password-input");
  const toggleBtn = document.getElementById("togglePassword");

  toggleBtn.addEventListener("click", function () {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    this.textContent = type === "password" ? "👁️" : "👁️‍🗨️";
  });

  // Clear Material Dashboard auto-outline
  passwordInput.addEventListener("input", () => {
    passwordInput.classList.remove("is-valid", "is-invalid");
  });

  const emailInput = document.getElementById("email-input");
  emailInput.addEventListener("input", () => {
    emailInput.classList.remove("is-valid", "is-invalid");
  });
});
});


</script>

  <!-- Custom Styles -->
  <style>
/* Normal (not focused) state */
.input-group.input-group-outline .form-control {
  border-bottom: 1px solid #aaa; /* Change this to your desired default color */
}

/* Focused state (Material JS adds .is-focused) */
.input-group.input-group-outline.is-focused .form-label {
  color:rgb(121, 146, 127) !important; /* Label color on focus (green in this example) */
}

.input-group.input-group-outline.is-focused .form-control {
  border-color:rgb(124, 168, 134) !important; /* Outline/underline color on focus */
  box-shadow: none;
}
/* Outlined Alert Styles (Material UI Style) */
/* Filled Alerts (Mimicking Material UI's "filled" variant) */
.alert.filled-error {
  background-color: #f44336;
  color: #fff;
  border: none;
}

.alert.filled-success {
  background-color: #4caf50;
  color: #fff;
  border: none;
}

.alert.filled-info {
  background-color: #2196f3;
  color: #fff;
  border: none;
}

.alert.filled-warning {
  background-color: #ff9800;
  color: #fff;
  border: none;
}

</style>




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
