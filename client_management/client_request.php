<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

// List of 24 barangays of Bago City
$barangays = [
    "Abuanan", "Alianza", "Atipuluan", "Bacong", "Bagroy", "Balingasag", "Binubuhan",
    "Busay", "Calumangan", "Caridad", "Dulao", "Ilijan", "Lag-asan", "Ma-ao", "Malingin",
    "Napoles", "Pacol", "Poblacion", "Sagasa", "Tabunan", "Taloc", "Tampalon", "Tuyom", "Mailum"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Request Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    // Email validation: must contain @ and end with gmail.com
    function validateEmail(email) {
        return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
    }
    // Contact validation: must start with 0 and be 11 digits
    function validateContact(contact) {
        return /^0\d{10}$/.test(contact);
    }
    function validateForm() {
        const email = document.getElementById('client_email').value.trim();
        const contact = document.getElementById('client_contact').value.trim();
        const address = document.getElementById('client_address').value;

        let valid = true;
        let msg = "";

        if (!validateEmail(email)) {
            msg += "Email must be a valid Gmail address (e.g., example@gmail.com).\n";
            valid = false;
        }
        if (!validateContact(contact)) {
            msg += "Contact number must start with 0 and be exactly 11 digits.\n";
            valid = false;
        }
        if (!address) {
            msg += "Please select your barangay address.\n";
            valid = false;
        }
        if (!valid) {
            alert(msg);
        }
        return valid;
    }
    </script>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <?php include '../sidebar/client_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white text-center">
                            <h4>Client Request Form</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['msg'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                            <?php endif; ?>
                            <form method="POST" action="submit_request.php" onsubmit="return validateForm();">
                                <div class="mb-3">
                                    <label for="client_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="client_name" name="client_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="client_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="client_email" name="client_email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="client_contact" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="client_contact" name="client_contact" maxlength="11" required>
                                </div>
                                    <div class="mb-3">
                                        <label for="request_details" class="form-label">Request Details</label>
                                        <select class="form-select form-select-sm" id="request_details" name="request_details" onchange="toggleOtherRequest()" required>
                                            <option value="">-- Select Request Type --</option>
                                            <option value="Grass-Cutting">Grass-Cutting</option>
                                            <option value="Garbage Collection">Garbage Collection</option>
                                            <option value="Cutting of Trees">Cutting of Trees</option>
                                            <option value="Prunning of Trees">Prunning of Trees</option>
                                            <option value="Other">Other (please specify below)</option>
                                        </select>
                                        <input type="text" class="form-control mt-2 d-none" id="other_request" name="other_request" placeholder="Please specify your request">
                                        </div>
                                            <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                                            </form>
                                        </div>
                                    </div>
                                <script>
                                    function toggleOtherRequest() {
                                    var select = document.getElementById('request_details');
                                    var otherInput = document.getElementById('other_request');
                                        if (select.value === 'Other') {
                                            otherInput.classList.remove('d-none');
                                            otherInput.required = true;
                                        } else {
                                            otherInput.classList.add('d-none');
                                            otherInput.required = false;
                                            otherInput.value = '';
                                        }
                                    }
                                    // Update validation to remove address check
                                    function validateForm() {
                                        const email = document.getElementById('client_email').value.trim();
                                        const contact = document.getElementById('client_contact').value.trim();
                                        const requestDetails = document.getElementById('request_details').value;
                                        const otherRequest = document.getElementById('other_request').value.trim();

                                        let valid = true;
                                        let msg = "";

                                        if (!validateEmail(email)) {
                                            msg += "Email must be a valid Gmail address (e.g., example@gmail.com).\n";
                                            valid = false;
                                        }
                                        if (!validateContact(contact)) {
                                            msg += "Contact number must start with 0 and be exactly 11 digits.\n";
                                            valid = false;
                                        }
                                        if (!requestDetails) {
                                            msg += "Please select a request type.\n";
                                            valid = false;
                                        }
                                        if (requestDetails === "Other" && !otherRequest) {
                                            msg += "Please specify your request in the text box.\n";
                                            valid = false;
                                        }
                                        if (!valid) {
                                        alert(msg);
                                        }
                                        return valid;
                                        }
                                </script>
                    </div>
                </div>
            </div>

        <!-- Footer -->
        <?php include '../includes/footer.php';