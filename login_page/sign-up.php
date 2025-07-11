<!-- filepath: c:\xampp\phpMyAdmin\CEMO_System\final\login_page\sign-up.php -->
<div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="signUpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="signUpModalLabel">Create an Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="sign-up-process.php" method="POST" id="signupForm">
          
          <!-- First Name -->
          <div class="input-group input-group-outline mb-3">
              <label class="form-label" for="signup-firstname">First Name</label>
              <input type="text" class="form-control" id="signup-firstname" name="first_name" required>
           <span class="input-group-text validation-icon" style="margin-top: -1%;"></span>
          </div>

          <!-- Last Name -->
          <div class="input-group input-group-outline mb-3">
              <label class="form-label" for="signup-lastname">Last Name</label>
              <input type="text" class="form-control" id="signup-lastname" name="last_name" required>
              <span class="input-group-text validation-icon" style="margin-top: -1%;"></span>
              </div>

          <!-- Email Address -->
          <div class="input-group input-group-outline mb-3 position-relative">
              <label class="form-label" for="signup-email">Email Address</label>
              <input type="email" class="form-control" id="signup-email" name="email" required>
              <span class="input-group-text validation-icon" style="margin-top: -1%;"></span>
              <small id="email-error" class="text-danger position-absolute" style="top: 100%;"></small>
          </div>

          <!-- Phone Number -->
          <div class="input-group input-group-outline mb-3 position-relative">
              <label class="form-label" for="signup-phone">Phone Number</label>
              <input type="tel" class="form-control pe-5" id="signup-phone" name="phone" 
                     maxlength="11" minlength="11" pattern="^\d{11}$" required 
                     oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                     <span class="input-group-text validation-icon" style="margin-top: -1%;"></span>
                     </div>

          <!-- Barangay Dropdown -->
          <div class="input-group input-group-outline mb-3">
              <label class="form-label" for="signup-barangay"></label>
              <select class="form-select form-control" id="signup-barangay" name="barangay" required>
                  <option value="" disabled selected>Select your barangay</option>
                  <?php
                  require_once '../includes/conn.php'; // Ensure this file initializes $pdo
                  try {
                      // Query to fetch barangays from the database
                      $stmt = $pdo->query("SELECT barangay FROM barangays_table"); 
                      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                          // Populate the dropdown with barangay names
                          echo '<option value="' . htmlspecialchars($row['barangay']) . '">' . htmlspecialchars($row['barangay']) . '</option>';
                      }
                  } catch (PDOException $e) {
                      // Handle errors gracefully
                      echo '<option value="" disabled>Error loading barangays</option>';
                  }
                  ?>
              </select>
              <span class="input-group-text validation-icon" style="margin-top: -1%;"></span>
              </div>
              <small id="passwordHelp" class="text-muted">Must be at least 8 characters, include a number & symbol.</small>

          <!-- Password -->
          <div class="input-group input-group-outline mb-3 position-relative">
              <label for="signup-password" class="form-label">Password</label>
              <input type="password" class="form-control pe-5" id="signup-password" name="password" required>
              <span class="input-group-text validation-icon position-absolute" 
              style="top: 50%; right: 45px; transform: translateY(-50%);"></span>
              <span class="input-group-text position-absolute toggle-password" 
                      style="top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;">
                  👁️
              </span>
          </div>

          <!-- Terms and Conditions -->

          <!-- Submit Button -->
          <button type="submit" class="btn btn-lg bg-gradient-dark btn-lg w-100 mt-4 mb-0" id="signup-submit">Register</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-password").forEach(toggle => {
        toggle.addEventListener("click", function () {
            let passwordInput = this.closest(".input-group").querySelector("input");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                this.innerHTML = "👁️‍🗨️"; // Open eye icon
            } else {
                passwordInput.type = "password";
                this.innerHTML = "👁️"; // Closed eye icon
            }
        });
    });
});

// Real-Time Validation Script
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("signupForm");
    const inputs = form.querySelectorAll("input, select");
    const submitButton = document.getElementById("signup-submit");
    const emailInput = document.getElementById("signup-email");
    const emailError = document.getElementById("email-error");

    function validateInput(input) {
        let isValid = false;
        const value = input.value.trim();
        const icon = input.nextElementSibling;

        if (input.type === "text") {
            isValid = value.length > 2;
        } else if (input.type === "email") {
            isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        } else if (input.type === "tel") {
            isValid = /^[0-9]{11}$/.test(value);
        } else if (input.id === "signup-password") {
            isValid = value.length >= 8 && /[0-9]/.test(value) && /[\W_]/.test(value);
        } else if (input.tagName === "SELECT") {
            isValid = input.selectedIndex > 0;
        } else if (input.type === "checkbox") {
            isValid = input.checked;
        }

        if (icon) {
            icon.innerHTML = isValid ? "✅" : "";
        }

        return isValid;
    }

    function checkFormValidity() {
        let allValid = true;
        inputs.forEach(input => {
            if (!validateInput(input)) allValid = false;
        });

        submitButton.disabled = !allValid;
    }

    inputs.forEach(input => {
        input.addEventListener("input", checkFormValidity);
        input.addEventListener("change", checkFormValidity);
    });

    checkFormValidity();

    // AJAX Email Validation
    emailInput.addEventListener("input", function () {
        const email = emailInput.value.trim();
        if (email === "") return;

        fetch("check-email.php?email=" + encodeURIComponent(email))
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    emailError.textContent = "Email is already registered!";
                    emailInput.nextElementSibling.innerHTML = "❌";
                    submitButton.disabled = true;
                } else {
                    emailError.textContent = "";
                    emailInput.nextElementSibling.innerHTML = "✅";
                    checkFormValidity();
                }
            })
            .catch(error => console.error("Error:", error));
    });
});
</script>
