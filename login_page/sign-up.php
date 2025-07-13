<!-- filepath: c:\xampp\phpMyAdmin\CEMO_System\final\login_page\sign-up.php -->
    <div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="signUpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-center modal-title w-100" id="signUpModalLabel">Create an Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-0">
                <p class="text-center mb-3">Please fill in the form below to create an account.</p>
                <form action="sign-up-process.php" method="POST" id="signupForm" autocomplete="off">
                <!-- First Name -->
                <div class="input-group input-group-outline mb-3 position-relative">
                    <label class="form-label" for="signup-firstname">First Name</label>
                    <input type="text" class="form-control" id="signup-firstname" name="first_name" required>
                    <span class="input-group-text validation-icon position-absolute" id="firstname-icon" style="top: 50%; right: 10px; transform: translateY(-50%);"></span>
                </div>
                <!-- Last Name -->
                <div class="input-group input-group-outline mb-3 position-relative">
                    <label class="form-label" for="signup-lastname">Last Name</label>
                    <input type="text" class="form-control" id="signup-lastname" name="last_name" required>
                    <span class="input-group-text validation-icon position-absolute" id="lastname-icon" style="top: 50%; right: 10px; transform: translateY(-50%);"></span>
                </div>
                <!-- Email Address -->
                <div class="input-group input-group-outline mb-3 position-relative">
                    <label class="form-label" for="signup-email">Email Address</label>
                    <input type="email" class="form-control" id="signup-email" name="email" required>
                    <span class="input-group-text validation-icon position-absolute" id="email-icon" style="top: 50%; right: 10px; transform: translateY(-50%);"></span>
                </div>
                <small id="email-error" class="text-danger d-block mt-1 mb-1"></small>  
                <small id="email-format-hint" class="text-muted d-block mb-2" style="display: none;"></small>

                <!-- Phone Number -->
                <div class="input-group input-group-outline mb-3 position-relative">
                    <label class="form-label" for="signup-contact">Contact Number</label>
                    <input type="tel" class="form-control" id="signup-contact" name="contact"
                    maxlength="11" minlength="11" pattern="^\d{11}$" required
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                    <span class="input-group-text validation-icon position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%);"></span>
                </div>
                <small id="contact-error" class="text-danger d-block mt-1 mb-1"></small>
                <!-- Barangay Dropdown -->
                <div class="input-group input-group-outline mb-3">
                    <label class="form-label" for="signup-barangay"></label>
                    <select class="form-select form-control" id="signup-barangay" name="barangay" required>
                    <option value="" disabled selected>Select your barangay</option>
                    <?php
                    require_once '../includes/conn.php';
                    try {
                        $stmt = $pdo->query("SELECT barangay FROM barangays_table");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row['barangay']) . '">' . htmlspecialchars($row['barangay']) . '</option>';
                        }
                    } catch (PDOException $e) {
                        echo '<option value="" disabled>Error loading barangays</option>';
                    }
                    ?>
                    </select>
                </div>
                <small id="passwordHelp" class="text-muted mb-2 d-block">Must be at least 8 characters, include a number & symbol.</small>
                <!-- Password -->
                <div class="input-group input-group-outline mb-3 position-relative">
                    <label for="signup-password" class="form-label">Password</label>
                    <input type="password" class="form-control pe-5" id="signup-password" name="password" required>
                    <span class="input-group-text validation-icon position-absolute" style="top: 50%; right: 45px; transform: translateY(-50%);"></span>
                    <span class="input-group-text position-absolute toggle-password" style="top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;">👁️</span>
                </div>
                <!-- Submit Button -->
                <button type="submit" class="btn btn-lg bg-gradient-dark w-100 mt-3 mb-0" id="signup-submit" disabled>Register</button>
                </form>
            </div>
            </div>
        </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            // Toggle password visibility
            document.querySelectorAll(".toggle-password").forEach(toggle => {
                toggle.addEventListener("click", function () {
                let passwordInput = this.closest(".input-group").querySelector("input[type='password'], input[type='text']");
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    this.innerHTML = "👁️‍🗨️";
                } else {
                    passwordInput.type = "password";
                    this.innerHTML = "👁️";
                }
                });
            });

          // Real-Time Validation
            const form = document.getElementById("signupForm");
            const submitButton = document.getElementById("signup-submit");
            const emailInput = document.getElementById("signup-email");
            const emailError = document.getElementById("email-error");
            const emailIcon = document.getElementById("email-icon");
            const contactInput = document.getElementById("signup-contact");
            const contactError = document.getElementById("contact-error");
            const contactIcon = contactInput.parentElement.querySelector(".validation-icon");
            const passwordInput = document.getElementById("signup-password");
            const passwordIcon = passwordInput.parentElement.querySelector(".validation-icon");
            const firstNameInput = document.getElementById("signup-firstname");
            const lastNameInput = document.getElementById("signup-lastname");
            const firstnameIcon = document.getElementById("firstname-icon");
            const lastnameIcon = document.getElementById("lastname-icon");
            const barangayInput = document.getElementById("signup-barangay");

            let emailValid = false, contactValid = false, passwordValid = false, firstNameValid = false, lastNameValid = false, barangayValid = false;

            // Helper validation functions
            function validateFirstName() {
                if (firstNameInput.value.trim().length > 1) {
                firstnameIcon.textContent = "✅";
                firstNameValid = true;
                } else {
                firstnameIcon.textContent = "";
                firstNameValid = false;
                }
                updateSubmit();
            }

            function validateLastName() {
                if (lastNameInput.value.trim().length > 1) {
                lastnameIcon.textContent = "✅";
                lastNameValid = true;
                } else {
                lastnameIcon.textContent = "";
                lastNameValid = false;
                }
                updateSubmit();
            }

            function validateEmail() {
                const value = emailInput.value.trim().toLowerCase();
                const formatHint = document.getElementById("email-format-hint");

                // Only allow emails ending with @gmail.com
                if (!/^([a-zA-Z0-9_.+-])+@gmail\.com$/.test(value)) {
                emailError.textContent = "";
                emailIcon.textContent = "";
                formatHint.style.display = value.length > 0 ? "block" : "none";
                emailValid = false;
                updateSubmit();
                return;
                } else {
                formatHint.style.display = "none";
                }

                // AJAX check for email uniqueness
                fetch("check-email.php?email=" + encodeURIComponent(value))
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                    emailError.textContent = "Email is already registered!";
                    emailIcon.textContent = "❌";
                    emailValid = false;
                    } else {
                    emailError.textContent = "";
                    emailIcon.textContent = "✅";
                    emailValid = true;
                    }
                    updateSubmit();
                })
                .catch(() => {
                    emailError.textContent = "Could not check email. Please try again.";
                    emailIcon.textContent = "❌";
                    emailValid = false;
                    updateSubmit();
                });
            }


        function validateContact() {
            const value = contactInput.value.trim();
            if (!/^\d{11}$/.test(value)) {
            contactError.textContent = "Contact must be 11 digits.";
            contactIcon.textContent = "❌";
            contactValid = false;
            updateSubmit();
            return;
            }
            // AJAX check for contact uniqueness
            fetch("check-phone.php?contact=" + encodeURIComponent(value))
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                contactError.textContent = "Contact is already registered!";
                contactIcon.textContent = "❌";
                contactValid = false;
                } else {
                contactError.textContent = "";
                contactIcon.textContent = "✅";
                contactValid = true;
                }
                updateSubmit();
            })
            .catch(() => {
                contactError.textContent = "Could not check contact. Please try again.";
                contactIcon.textContent = "❌";
                contactValid = false;
                updateSubmit();
            });
        }

        function validatePassword() {
            const value = passwordInput.value;
            if (value.length >= 8 && /[0-9]/.test(value) && /[\W_]/.test(value)) {
            passwordIcon.textContent = "✅";
            passwordValid = true;
            } else {
            passwordIcon.textContent = "❌";
            passwordValid = false;
            }
            updateSubmit();
        }

        function validateBarangay() {
            barangayValid = barangayInput.value !== "";
            updateSubmit();
        }

        function updateSubmit() {
            submitButton.disabled = !(emailValid && contactValid && passwordValid && firstNameValid && lastNameValid && barangayValid);
        }

        // Event listeners
        firstNameInput.addEventListener("input", validateFirstName);
        lastNameInput.addEventListener("input", validateLastName);
        emailInput.addEventListener("input", validateEmail);
        contactInput.addEventListener("input", validateContact);
        passwordInput.addEventListener("input", validatePassword);
        barangayInput.addEventListener("change", validateBarangay);

        // Initial validation
        validateBarangay();
        validatePassword();
        updateSubmit();

        // Prevent form submit if not valid
        form.addEventListener("submit", function (e) {
            if (submitButton.disabled) {
            e.preventDefault();
            }
        });
        });
        </script>

        <style>
        .email-error {
        position: absolute;
        }
        .input-group-outline .form-control {
        padding-right: 2.5rem;
        }
        .validation-icon {
        pointer-events: none;
        font-size: 1rem;
        }
        .modal-title {
        width: 100%;
        text-align: center;
        }
        .input-group input:invalid, .input-group select:invalid {
        border-color:rgb(74, 62, 64) !important;
        }
        .input-group input:valid, .input-group select:valid {
        border-color: #28a745 !important;
        }
        .input-group input, .input-group select {
        border-width: 2px;
        border-style: solid;
        border-color: #ced4da;
        transition: border-color 0.2s;
        }
        .input-group .form-label {
        font-weight: 500;
        }
        .input-group .form-control:focus {
        border-color: #007bff;
        box-shadow: none;
        }
        .input-group-outline .form-control {
        padding-right: 2.5rem;
        }
        .validation-icon {
        pointer-events: none;
        font-size: 1rem;
        }
        small.text-danger,
        small.text-muted {
        font-size: 0.85rem;
        }
        small.text-danger {
        color: #dc3545;
        }
        .input-group-outline {
        margin-bottom: 0.25rem; /* Reduce spacing here */
        }
        .input-group + small {
        margin-left: 0.25rem;
        margin-top: 0.25rem;
        }
        </style>