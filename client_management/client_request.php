<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

// Get client information
$client_id = $_SESSION['client_id'];
$stmt = $pdo->prepare("SELECT * FROM client_table WHERE client_id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

// Get available dates (excluding weekends and holidays)
function getAvailableDates() {
    $available_dates = [];
    $current_date = new DateTime();
    $end_date = new DateTime();
    $end_date->add(new DateInterval('P30D')); // 30 days from now
    
    while ($current_date <= $end_date) {
        $day_of_week = $current_date->format('N'); // 1 (Monday) through 7 (Sunday)
        
        // Available on weekdays (Monday to Friday)
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            $available_dates[] = $current_date->format('Y-m-d');
        }
        
        $current_date->add(new DateInterval('P1D'));
    }
    
    return $available_dates;
}

$available_dates = getAvailableDates();

if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_notes = $_POST['admin_notes'];

    // Define status based on action
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        $status = 'pending'; // fallback
    }

    // Set this to use in alert
    $new_status = ucfirst($status);

    $stmt = $conn->prepare("UPDATE client_requests SET status = ?, admin_notes = ? WHERE request_id = ?");
    $stmt->bind_param("ssi", $status, $admin_notes, $request_id);
    if ($stmt->execute()) {
        $_SESSION['client_alert'] = [
            'title' => 'Success!',
            'text' => "Request has been $new_status.",
            'icon' => 'success'
        ];
    } else {
        $_SESSION['client_alert'] = [
            'title' => 'Error!',
            'text' => 'Something went wrong while updating the request.',
            'icon' => 'error'
        ];
    }
    header("Location: client_request.php"); // << Add this
exit();
}






?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Request Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .calendar-container {
            background: #f8f9fa;
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .calendar-day {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 2px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }
        .available-day {
            background: #28a745;
            color: white;
        }
        .unavailable-day {
            background: #dc3545;
            color: white;
        }
        .selected-day {
            background: #007bff;
            color: white;
            border: 2px solid #0056b3;
        }
        .requirements-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .form-body {
            padding: 30px;
        }
        .client-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .client-info h6 {
            color: #495057;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .info-value {
            color: #495057;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Sidebar -->
    <?php include '../sidebar/client_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="form-container">
                        <div class="form-header">
                            <h3><i class="fas fa-clipboard-list me-2"></i>Service Request Form</h3>
                            <p class="mb-0">Submit your service request and schedule</p>
                        </div>
                        
                        <div class="form-body">
                            <?php if (isset($_SESSION['msg'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['msg']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['msg']); ?>
                            <?php endif; ?>

                            <!-- Client Information Display -->
                            <div class="client-info">
                                <h6><i class="fas fa-user me-2"></i>Client Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <span class="info-label">Full Name:</span>
                                            <span class="info-value"><?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Email:</span>
                                            <span class="info-value"><?= htmlspecialchars($client['email']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <span class="info-label">Contact:</span>
                                            <span class="info-value"><?= htmlspecialchars($client['contact']) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Barangay:</span>
                                            <span class="info-value"><?= htmlspecialchars($client['barangay']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="submit_request.php" id="requestForm">
                                <input type="hidden" name="client_id" value="<?= $client_id ?>">
                                <input type="hidden" name="client_name" value="<?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>">
                                <input type="hidden" name="client_email" value="<?= htmlspecialchars($client['email']) ?>">
                                <input type="hidden" name="client_contact" value="<?= htmlspecialchars($client['contact']) ?>">
                                <input type="hidden" name="client_barangay" value="<?= htmlspecialchars($client['barangay']) ?>">

                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Request Type Selection -->
                                        <div class="mb-4">
                                            <label for="request_type" class="form-label fw-bold">
                                                <i class="fas fa-tasks me-2"></i>Service Type
                                            </label>
                                            <select class="form-select form-select-lg" id="request_type" name="request_type" required onchange="showRequirements()">
                                                <option value="">-- Select Service Type --</option>
                                                <option value="Grass-Cutting">Grass-Cutting</option>
                                                <option value="Garbage Collection">Garbage Collection</option>
                                                <option value="Cutting of Trees">Cutting of Trees</option>
                                                <option value="Pruning of Trees">Pruning of Trees</option>
                                                <option value="Street Cleaning">Street Cleaning</option>
                                                <option value="Drainage Maintenance">Drainage Maintenance</option>
                                                <option value="Other">Other (please specify)</option>
                                            </select>
                                        </div>

                                        <!-- Dynamic Requirements Box -->
                                        <div id="requirementsBox" class="requirements-box" style="display: none;">
                                            <h6><i class="fas fa-info-circle me-2"></i>Requirements for this service:</h6>
                                            <div id="requirementsList"></div>
                                        </div>

                                        <!-- Other Request Details -->
                                        <div class="mb-4" id="otherRequestDiv" style="display: none;">
                                            <label for="other_request" class="form-label fw-bold">
                                                <i class="fas fa-edit me-2"></i>Please specify your request
                                            </label>
                                            <textarea class="form-control" id="other_request" name="other_request" rows="3" placeholder="Please provide detailed description of your request..."></textarea>
                                        </div>

                                        <!-- Request Description -->
                                        <div class="mb-4">
                                            <label for="request_description" class="form-label fw-bold">
                                                <i class="fas fa-comment me-2"></i>Additional Details
                                            </label>
                                            <textarea class="form-control" id="request_description" name="request_description" rows="4" placeholder="Please provide any additional details about your request..."></textarea>
                                        </div>

                                        <!-- Preferred Date Selection -->
                                        <div class="mb-4">
                                            <label for="request_date" class="form-label fw-bold">
                                                <i class="fas fa-calendar me-2"></i>Preferred Date
                                            </label>
                                            <input type="text" class="form-control" id="request_date" name="request_date" placeholder="Select your preferred date" required readonly>
                                        </div>

                                        <!-- Preferred Time Selection -->
                                        <div class="mb-4">
                                            <label for="preferred_time" class="form-label fw-bold">
                                                <i class="fas fa-clock me-2"></i>Preferred Time
                                            </label>
                                            <select class="form-control" id="preferred_time" name="preferred_time" required>
                                                <option value="" disabled selected>Select a preferred time</option>
                                                <option value="08:00">8:00 AM</option>
                                                <option value="09:00">9:00 AM</option>
                                                <option value="10:00">10:00 AM</option>
                                                <option value="11:00">11:00 AM</option>
                                                <option value="12:00">12:00 PM</option>
                                                <option value="13:00">1:00 PM</option>
                                                <option value="14:00">2:00 PM</option>
                                            </select>
                                        </div>


                                        <!-- Submit Button -->
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Calendar Section -->
                                    <div class="col-md-4">
                                        <div class="calendar-container">
                                            <h6 class="text-center mb-3">
                                                <i class="fas fa-calendar-alt me-2"></i>Available Dates
                                            </h6>
                                            <div class="text-center mb-3">
                                                <small class="text-muted">
                                                    <span class="badge bg-success me-2">●</span>Available
                                                    <span class="badge bg-secondary ms-2">●</span>Unavailable
                                                </small>
                                            </div>
                                            <!-- Calendar Grid -->
                                            <div id="calendar" class="text-center"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include '../includes/footer.php'; ?>
    </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Your other scripts -->
    
<script>
        // Available dates from PHP
        const availableDates = <?= json_encode($available_dates) ?>;
        
        const serviceRequirements = {
            'Grass-Cutting': [
                'Clear area of any obstacles',
                'Ensure pets are secured',
                'Remove any valuable items from the area'
            ],
            'Garbage Collection': [
                'Properly segregate waste',
                'Place garbage in designated area',
                'Ensure bags are properly sealed'
            ],
            'Cutting of Trees': [
                'Obtain necessary permits',
                'Clear area around the tree',
                'Ensure no power lines nearby',
                'Provide access for equipment'
            ],
            'Pruning of Trees': [
                'Clear area around the tree',
                'Ensure no power lines nearby',
                'Provide access for equipment'
            ],
            'Street Cleaning': [
                'Move vehicles from the street',
                'Clear any obstacles',
                'Ensure proper drainage'
            ],
            'Drainage Maintenance': [
                'Clear area around drainage',
                'Ensure proper access',
                'Remove any blockages if possible'
            ]
        };

        // Initialize calendar
        function initializeCalendar() {
            const calendar = document.getElementById('calendar');
            const currentDate = new Date();
            const currentMonth = currentDate.getMonth();
            const currentYear = currentDate.getYear() + 1900;
            
            let calendarHTML = `
                <div class="mb-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="previousMonth()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="fw-bold mx-3">${getMonthName(currentMonth)} ${currentYear}</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="nextMonth()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="mb-2">
                    <span class="calendar-day fw-bold text-muted">S</span>
                    <span class="calendar-day fw-bold text-muted">M</span>
                    <span class="calendar-day fw-bold text-muted">T</span>
                    <span class="calendar-day fw-bold text-muted">W</span>
                    <span class="calendar-day fw-bold text-muted">T</span>
                    <span class="calendar-day fw-bold text-muted">F</span>
                    <span class="calendar-day fw-bold text-muted">S</span>
                </div>
            `;
            
            // Generate calendar days
            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            for (let week = 0; week < 6; week++) {
                calendarHTML += '<div class="mb-1">';
                for (let day = 0; day < 7; day++) {
                    const currentDay = new Date(startDate);
                    currentDay.setDate(startDate.getDate() + (week * 7) + day);
                    
                    const dateString = currentDay.toISOString().split('T')[0];
                    const isAvailable = availableDates.includes(dateString);
                    const isCurrentMonth = currentDay.getMonth() === currentMonth;
                    const isToday = dateString === new Date().toISOString().split('T')[0];
                    
                    let dayClass = 'calendar-day';
                    if (!isCurrentMonth) {
                        dayClass += ' text-muted';
                    } else if (isAvailable) {
                        dayClass += ' available-day';
                    } else {
                        dayClass += ' unavailable-day';
                    }
                    
                    if (isToday) {
                        dayClass += ' border border-primary';
                    }
                    
                    calendarHTML += `<span class="${dayClass}" onclick="selectDate('${dateString}')" title="${dateString}">${currentDay.getDate()}</span>`;
                }
                calendarHTML += '</div>';
            }
            
            calendar.innerHTML = calendarHTML;
        }

        function getMonthName(month) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
            return months[month];
        }

        function selectDate(dateString) {
            if (availableDates.includes(dateString)) {
                document.getElementById('request_date').value = dateString;
                // Update calendar selection
                document.querySelectorAll('.calendar-day').forEach(day => {
                    day.classList.remove('selected-day');
                });
                event.target.classList.add('selected-day');
            }
        }

        function showRequirements() {
            const requestType = document.getElementById('request_type').value;
            const requirementsBox = document.getElementById('requirementsBox');
            const requirementsList = document.getElementById('requirementsList');
            const otherRequestDiv = document.getElementById('otherRequestDiv');
            const otherRequest = document.getElementById('other_request');
            
            if (requestType === 'Other') {
                requirementsBox.style.display = 'none';
                otherRequestDiv.style.display = 'block';
                otherRequest.required = true;
            } else if (requestType && serviceRequirements[requestType]) {
                requirementsBox.style.display = 'block';
                otherRequestDiv.style.display = 'none';
                otherRequest.required = false;
                
                let requirementsHTML = '<ul class="mb-0">';
                serviceRequirements[requestType].forEach(req => {
                    requirementsHTML += `<li>${req}</li>`;
                });
                requirementsHTML += '</ul>';
                requirementsList.innerHTML = requirementsHTML;
            } else {
                requirementsBox.style.display = 'none';
                otherRequestDiv.style.display = 'none';
                otherRequest.required = false;
            }
        }

        // Initialize flatpickr for date input
        flatpickr("#request_date", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: [
                function(date) {
                    // Disable weekends
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ],
            onChange: function(selectedDates, dateStr) {
                // Update calendar selection
                document.querySelectorAll('.calendar-day').forEach(day => {
                    day.classList.remove('selected-day');
                    if (day.getAttribute('title') === dateStr) {
                        day.classList.add('selected-day');
                    }
                });
            }
        });

        // Initialize calendar on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
        });

        // Form validation
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const requestType = document.getElementById('request_type').value;
            const preferredDate = document.getElementById('request_date').value;
            const otherRequest = document.getElementById('other_request').value;
            
            if (!requestType) {
                e.preventDefault();
                alert('Please select a service type.');
                return false;
            }
            
            if (requestType === 'Other' && !otherRequest.trim()) {
                e.preventDefault();
                alert('Please specify your request details.');
                return false;
            }
            
            if (!preferredDate) {
                e.preventDefault();
                alert('Please select a preferred date.');
                return false;
            }
            
            if (!availableDates.includes(preferredDate)) {
                e.preventDefault();
                alert('Please select an available date.');
                return false;
            }
        });
    </script>

    <!-- ✅ Place SweetAlert notification here -->
 <?php if (isset($_SESSION['msg'])): ?>
    <?php 
    $msg = $_SESSION['msg']; 
    unset($_SESSION['msg']); 

    // Determine icon based on content or message type
    $icon = strpos(strtolower($msg), 'error') !== false || strpos(strtolower($msg), 'failed') !== false ? 'error' : 'success';
    ?>
    <script>
        Swal.fire({
            title: <?= $icon === 'success' ? '""' : '"Notice"' ?>,
            text: <?= json_encode($msg) ?>,
            icon: '<?= $icon ?>',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>

<?php if (isset($_SESSION['client_alert'])): ?>
    <?php 
    $alert = $_SESSION['client_alert']; 
    unset($_SESSION['client_alert']); 
    ?>
    <script>
        Swal.fire({
            title: <?= json_encode($alert['title']) ?>,
            text: <?= json_encode($alert['text']) ?>,
            icon: <?= json_encode($alert['icon']) ?>,
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>
</body>
</html>
