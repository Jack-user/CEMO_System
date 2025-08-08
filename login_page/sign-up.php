<!-- filepath: c:\xampp\phpMyAdmin\CEMO_System\final\login_page\sign-up.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            height: 100vh;
            position: relative;
        }

        #id-preview-container {
            position: relative;
            width: 100%;
            max-width: 360px;
            margin: 10px auto;
            overflow: hidden;
        }

        .ocrloader p::before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #18c89b;
            position: relative;
            right: 4px;
        }

        .ocrloader p {
            color: #18c89b;
            position: absolute;
            bottom: -30px;
            left: 38%;
            font-size: 16px;
            font-weight: 600;
            animation: blinker 1.5s linear infinite;
            font-family: sans-serif;
            text-transform: uppercase;
        }

        .ocrloader {
            width: 100%;
            height: 200px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 0;
            backface-visibility: hidden;
        }

        .ocrloader span {
            position: absolute;
            left: 15%;
            top: 0;
            width: 70%;
            height: 5px;
            background-color: #18c89b;
            box-shadow: 0 0 10px 1px #18c89b,
                        0 0 1px 1px #18c89b;
            z-index: 1;
            transform: translateY(95px);
            animation: move 1.7s cubic-bezier(0.15, 0.54, 0.76, 0.74) infinite;
        }

        .ocrloader:before,
        .ocrloader:after,
        .ocrloader em:before,
        .ocrloader em:after {
            content: "";
            position: absolute;
            width: 45px;
            height: 46px;
            border-style: solid;
            border-width: 0;
            border-color: #18c89b;
        }

        .ocrloader:before {
            left: 0;
            top: 0;
            border-left-width: 5px;
            border-top-width: 5px;
            border-radius: 5px 0 0 0;
        }

        .ocrloader:after {
            right: 0;
            top: 0;
            border-right-width: 5px;
            border-top-width: 5px;
            border-radius: 0 5px 0 0;
        }

        .ocrloader em:before {
            left: 0;
            bottom: 0;
            border-left-width: 5px;
            border-bottom-width: 5px;
            border-radius: 0 0 0 5px;
        }

        .ocrloader em:after {
            right: 0;
            bottom: 0;
            border-right-width: 5px;
            border-bottom-width: 5px;
            border-radius: 0 0 5px 0;
        }

        @keyframes move {
            0%, 100% {
                transform: translateY(190px);
            }
            50% {
                transform: translateY(0);
            }
            75% {
                transform: translateY(160px);
            }
        }

        @keyframes blinker {  
            50% { opacity: 0; }
        }

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
            border-color: rgb(74, 62, 64) !important;
        }

        .input-group input:valid, .input-group select:valid {
            border-color: #28a745 !important;
        }

        .input-group input, .input-group select {
            border-width: 2px;
            border-style: solid;
            transition: border-color 0.2s;
        }

        .input-group .form-label {
            font-weight: 500;
        }

        .input-group .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
        }

        small.text-danger,
        small.text-muted {
            font-size: 0.85rem;
        }

        .input-group-outline {
            margin-bottom: 0.25rem;
        }

        .input-group + small {
            margin-left: 0.25rem;
            margin-top: 0.25rem;
        }

        /* Highlight mismatched fields in red */
        .mismatch {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }

        /* Retake button style */
        .retake-btn {
            font-size: 0.875rem;
            margin-top: 8px;
            padding: 0.25rem 0.5rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .ocrloader {
                height: 180px;
            }
            .ocrloader p {
                font-size: 14px;
                bottom: -25px;
                left: 30%;
            }
            #id-preview {
                max-height: 180px;
            }
        }
    </style>
</head>
<body>

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
                    <small id="email-format-hint" class="text-muted d-block mb-2" style="display: none;">Only @gmail.com emails allowed.</small>

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

                    <!-- ID Upload -->
                    <div class="mb-3">
                        <label for="id-upload" class="form-label">Upload Valid ID (Philippine)</label>
                        <input type="file" class="form-control form-control-sm" id="id-upload" accept="image/*">
                        <small class="form-text text-muted">We'll verify your identity using the ID.</small>

                        <!-- Preview Container with Scanner Overlay -->
                        <div class="position-relative mt-2" id="id-preview-container" style="display: none;">
                            <img id="id-preview" src="#" alt="ID Preview" class="w-100 rounded" style="max-height: 300px; object-fit: contain;">
                            <!-- OCR Scanner Animation Overlay -->
                            <div class="ocrloader" id="scanner-animation">
                                <span></span>
                                <p>Scanning</p>
                                <em></em>
                            </div>
                        </div>

                        <!-- Retake Button -->
                        <button type="button" id="retake-btn" class="btn btn-sm btn-outline-danger retake-btn" style="display: none;">📷 Retake ID</button>
                    </div>

                    <!-- OCR Status Message -->
                    <div id="ocr-status" class="text-muted small mb-2 text-center" style="display:none;"></div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-lg bg-gradient-dark w-100 mt-3 mb-0" id="signup-submit" disabled>Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/tesseract.js@v5.0.0/dist/tesseract.min.js"></script>

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

    // Real-Time Form Validation
    const form = document.getElementById("signupForm");
    const submitButton = document.getElementById("signup-submit");
    const firstNameInput = document.getElementById("signup-firstname");
    const lastNameInput = document.getElementById("signup-lastname");
    const firstnameIcon = document.getElementById("firstname-icon");
    const lastnameIcon = document.getElementById("lastname-icon");
    const emailInput = document.getElementById("signup-email");
    const emailError = document.getElementById("email-error");
    const emailIcon = document.getElementById("email-icon");
    const contactInput = document.getElementById("signup-contact");
    const contactError = document.getElementById("contact-error");
    const contactIcon = contactInput.parentElement.querySelector(".validation-icon");
    const passwordInput = document.getElementById("signup-password");
    const passwordIcon = passwordInput.parentElement.querySelector(".validation-icon");
    const barangayInput = document.getElementById("signup-barangay");

    let emailValid = false, contactValid = false, passwordValid = false, firstNameValid = false, lastNameValid = false, barangayValid = false;
    let ocrVerified = false;

    function validateFirstName() {
        if (firstNameInput.value.trim().length > 1) {
            firstNameValid = true;
            firstnameIcon.textContent = "✅";
        } else {
            firstNameValid = false;
            firstnameIcon.textContent = "";
        }
        updateSubmit();
    }

    function validateLastName() {
        if (lastNameInput.value.trim().length > 1) {
            lastNameValid = true;
            lastnameIcon.textContent = "✅";
        } else {
            lastNameValid = false;
            lastnameIcon.textContent = "";
        }
        updateSubmit();
    }

    function validateEmail() {
        const value = emailInput.value.trim().toLowerCase();
        const formatHint = document.getElementById("email-format-hint");
        if (!/^([a-zA-Z0-9_.+-])+@gmail\.com$/.test(value)) {
            emailError.textContent = "";
            emailIcon.textContent = "";
            formatHint.style.display = value.length > 0 ? "block" : "none";
            emailValid = false;
        } else {
            formatHint.style.display = "none";
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
                    emailError.textContent = "Could not check email.";
                    emailIcon.textContent = "❌";
                    emailValid = false;
                    updateSubmit();
                });
        }
        updateSubmit();
    }

    function validateContact() {
        const value = contactInput.value.trim();
        if (!/^\d{11}$/.test(value)) {
            contactError.textContent = "Contact must be 11 digits.";
            contactIcon.textContent = "❌";
            contactValid = false;
        } else {
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
                    contactError.textContent = "Could not check contact.";
                    contactIcon.textContent = "❌";
                    contactValid = false;
                    updateSubmit();
                });
        }
        updateSubmit();
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
        submitButton.disabled = !(emailValid && contactValid && passwordValid && firstNameValid && lastNameValid && barangayValid && ocrVerified);
    }

    // Event Listeners
    firstNameInput.addEventListener("input", validateFirstName);
    lastNameInput.addEventListener("input", validateLastName);
    emailInput.addEventListener("input", validateEmail);
    contactInput.addEventListener("input", validateContact);
    passwordInput.addEventListener("input", validatePassword);
    barangayInput.addEventListener("change", validateBarangay);

    validateBarangay();
    validatePassword();
    updateSubmit();

    form.addEventListener("submit", function (e) {
        if (submitButton.disabled) e.preventDefault();
    });

    // ID Upload & OCR Verification
    const idUpload = document.getElementById('id-upload');
    const retakeBtn = document.getElementById('retake-btn');
    const container = document.getElementById('id-preview-container');
    const preview = document.getElementById('id-preview');
    const scanner = document.getElementById('scanner-animation');
    const ocrStatus = document.getElementById('ocr-status');

    // Retake ID
    retakeBtn.addEventListener('click', function () {
        idUpload.value = "";
        container.style.display = 'none';
        retakeBtn.style.display = 'none';
        ocrStatus.style.display = 'none';
        submitButton.disabled = true;
        ocrVerified = false;
        firstNameInput.classList.remove("mismatch");
        lastNameInput.classList.remove("mismatch");
    });

    idUpload.addEventListener('change', function () {
        const file = this.files[0];
        const fname = firstNameInput.value.trim().toLowerCase();
        const lname = lastNameInput.value.trim().toLowerCase();

        if (!file || !fname || !lname) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Input',
                text: 'Please fill out first and last name before uploading the ID.'
            });
            this.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = function () {
            const img = new Image();
            img.onload = function () {
                preview.src = reader.result;
                container.style.display = 'block';
                retakeBtn.style.display = 'inline-block';
                ocrStatus.style.display = 'block';
                ocrStatus.textContent = 'Scanning ID...';
                scanner.style.display = 'block';

                runTesseractOCR(file, fname, lname);
            };
            img.src = reader.result;
        };
        reader.readAsDataURL(file);
    });

    function runTesseractOCR(file, fname, lname) {
        Swal.fire({
            title: 'Verifying ID...',
            html: 'Extracting text from the ID image.<br><b>Please wait.</b>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        Tesseract.recognize(file, 'eng', {
            logger: info => {
                if (info.status === "recognizing text") {
                    Swal.update({
                        html: `Extracting text... <b>${Math.round(info.progress * 100)}%</b>`
                    });
                }
            }
        }).then(({ data: { text } }) => {
            const normalize = str => str.toLowerCase().replace(/[^\w\s\-\/]/gi, '').replace(/\s+/g, ' ').trim();
            const cleanText = normalize(text);

            // ID number extraction
            const idNumberMatch = cleanText.match(/\b([A-Z0-9]{3,}-[A-Z0-9]{2,}-[A-Z0-9]{3,}(?:-[A-Z0-9]+)?)\b|\b\d{4}-\d{4}-\d{4}-\d{4}\b/);
            const extractedIdNumber = idNumberMatch ? idNumberMatch[0] : "";
            // Optional: document.getElementById('icn').value = extractedIdNumber;

            // ID type detection
            const idTypeMap = {
                "philippine national id": "Philippine National ID",
                "philsys": "Philippine National ID",
                "passport": "Passport",
                "driver": "Driver's License",
                "lto": "Driver's License",
                "umid": "UMID",
                "sss": "SSS ID",
                "prc": "PRC ID",
                "voter": "Voter's ID",
                "tin": "TIN ID",
                "philhealth": "PhilHealth ID"
            };

            let detectedType = "Unknown";
            for (const keyword in idTypeMap) {
                if (cleanText.includes(keyword)) {
                    detectedType = idTypeMap[keyword];
                    break;
                }
            }

            if (detectedType === "Unknown") {
                if (/^\d{4}-\d{4}-\d{4}-\d{4}$/.test(extractedIdNumber)) {
                    detectedType = "Philippine National ID";
                } else if (/^[A-Z]{1,3}-\d{2}-\d{6,7}$/.test(extractedIdNumber)) {
                    detectedType = "Driver's License";
                }
            }

            // Name matching
            const fnameMatch = cleanText.includes(fname);
            const lnameMatch = cleanText.includes(lname);

            scanner.style.display = 'none';

            if (fnameMatch && lnameMatch) {
                ocrVerified = true;
                firstNameInput.classList.remove("mismatch");
                lastNameInput.classList.remove("mismatch");
                firstnameIcon.textContent = "✅";
                lastnameIcon.textContent = "✅";

                Swal.fire({
                    icon: 'success',
                    title: 'ID Verified',
                    html: `
                        <div><b>Detected ID:</b> ${detectedType}</div>
                        <div class="mt-2">✅ Name matched successfully!</div>
                    `,
                    confirmButtonColor: '#198754'
                });
            } else {
                idUpload.value = "";
                container.style.display = 'none';
                retakeBtn.style.display = 'none';
                ocrVerified = false;
                firstNameInput.classList.add("mismatch");
                lastNameInput.classList.add("mismatch");

                const unmatched = [];
                if (!fnameMatch) unmatched.push("First Name");
                if (!lnameMatch) unmatched.push("Last Name");

                Swal.fire({
                    icon: 'error',
                    title: 'Name Mismatch',
                    html: `
                        <div><b>Detected ID:</b> ${detectedType}</div>
                        <div class="mt-2">❌ ${unmatched.join(", ")} not found on the ID.<br>The uploaded image has been cleared.</div>
                    `,
                    confirmButtonColor: '#dc3545'
                });
            }

            updateSubmit();
        }).catch(err => {
            console.error('OCR Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'OCR Failed',
                text: 'Could not read the ID. Please try again.',
                confirmButtonColor: '#dc3545'
            });
            submitButton.disabled = false; // Allow manual fallback
        });
    }
});
</script>

</body>
</html>