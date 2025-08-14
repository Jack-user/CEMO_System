<?php
session_start();
include '../includes/conn.php';
include '../includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

// Handle request approval/rejection
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_notes = $_POST['admin_notes'] ?? '';

    try {
        // Fetch the request details first (needed for insert and notification)
        $stmt = $pdo->prepare("SELECT * FROM client_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();

        if (!$request) {
            throw new Exception("Request not found.");
        }

        if ($action === 'approve') {
            $status = 'approved';
            $message = "Request approved successfully!";

            // Update request status
            $stmt = $pdo->prepare("
                UPDATE client_requests 
                SET status = ?, admin_notes = ?, updated_at = NOW() 
                WHERE request_id = ?
            ");
            $stmt->execute([$status, $admin_notes, $request_id]);

            // Insert into schedule_table as an Event with Scheduled status
            $insertEvent = $pdo->prepare("
                INSERT INTO schedule_table (event_name, day, time, status)
                VALUES (?, ?, ?, 'Scheduled')
            ");
            $insertEvent->execute([
                $request['request_details'],   // event_name
                $request['request_date'],      // day (YYYY-MM-DD)
                $request['request_time']       // time (HH:MM[:SS])
            ]);
        } else {
            // Reject logic
            $status = 'rejected';
            $message = "Request rejected.";

            $stmt = $pdo->prepare("
                UPDATE client_requests 
                SET status = ?, admin_notes = ?, updated_at = NOW() 
                WHERE request_id = ?
            ");
            $stmt->execute([$status, $admin_notes, $request_id]);
        }

        // Create notification for client
        $notification_message = $action === 'approve' 
            ? "Your request for {$request['request_details']} has been approved and scheduled for {$request['request_date']}."
            : "Your request for {$request['request_details']} has been rejected. Reason: {$admin_notes}";

        $stmt = $pdo->prepare("
            INSERT INTO client_notifications (
                client_id,
                notification_type,
                message,
                request_id,
                is_read,
                created_at
            ) VALUES (?, ?, ?, ?, 0, NOW())
        ");

        $stmt->execute([
            $request['client_id'],
            $action === 'approve' ? "Request Approved" : "Request Rejected",
            $notification_message,
            $request_id
        ]);

        // Mark admin notification as read
        $stmt = $pdo->prepare("
            UPDATE admin_notifications 
            SET is_read = 1 
            WHERE request_id = ? AND notification_type = 'request_update'
        ");
        $stmt->execute([$request_id]);

        $_SESSION['success_msg'] = $message;

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error updating request: " . $e->getMessage();
    }

    header("Location: admin_requests.php");
    exit();
}

// Get all requests
$stmt = $pdo->prepare("
    SELECT * FROM client_requests 
    ORDER BY submitted_at  DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();

// Prepare approved requests for JavaScript
$approved_requests = array_filter($requests, function($r) { 
    return $r['status'] === 'approved'; 
});
$approved_requests_json = json_encode(array_values($approved_requests));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Service Requests</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .request-card {
            transition: transform 0.2s;
        }
        .request-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .calendar-event {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin: 1px;
        }
        .calendar-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .calendar-toggle:hover {
            background-color: #f8f9fa;
        }
        .calendar-container {
            transition: all 0.3s ease;
        }
        .calendar-hidden {
            display: none;
        }
        .table-container {
            transition: all 0.3s ease;
        }
        .table-expanded {
            width: 100%;
        }
    </style>
</head>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= $_SESSION['success_msg'] ?>',
            confirmButtonColor: '#66c05eff'
        });
    });
</script>
<?php unset($_SESSION['success_msg']); ?>
<?php if (isset($_SESSION['error_msg'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $_SESSION['error_msg'] ?>',
            confirmButtonColor: '#dc3545'
        });
    });
</script>
<?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<body class="g-sidenav-show bg-gray-200">
    <?php include '../sidebar/admin_sidebar.php'; ?>
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg">
                        <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
                        <div style="background: linear-gradient(60deg, #66c05eff, #49755cff);" class="shadow-dark border-radius-lg pt-4 pb-3"> 
                        <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">
                                    <i class="fas fa-clipboard-list me-2"></i>Service Requests Management
                                </h5>
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            <?php if (isset($_SESSION['success_msg'])): ?>
                                <div class="alert alert-success alert-dismissible fade show mx-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success_msg']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['success_msg']); ?>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['error_msg'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error_msg']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['error_msg']); ?>
                            <?php endif; ?>
                            
                            <div class="row mx-4 mb-4">
                                <div class="col-md-8 table-container" id="tableContainer">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Service Requests</h6>
                                        <button class="btn btn-sm btn-outline-primary calendar-toggle" id="calendarToggle" onclick="toggleCalendar()">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <span id="toggleText">Show Approved</span>
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Client</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Request</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Date & Time</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Status</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($requests as $request): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center px-3 py-2">
                                                            <div><img src="../assets/img/logo.png" class="avatar avatar-sm rounded-circle me-3 shadow" alt="Client"></div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?= htmlspecialchars($request['client_name']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($request['client_email']) ?></p>
                                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($request['client_barangay']) ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($request['request_details']) ?></h6>
                                                            <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($request['request_description']) ?></p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            <?= date('M d, Y', strtotime($request['request_date'])) ?> 
                                                            <span class="text-sm"> - <?= date('h:i A', strtotime($request['request_time'])) ?></span>
                                                        </p>
                                                        <p class="text-xs text-secondary mb-0"><?= date('M d, Y H:i', strtotime($request['submitted_at'])) ?></p>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        $status_text = '';
                                                        switch ($request['status']) {
                                                            case 'pending':
                                                                $status_class = 'bg-warning';
                                                                $status_text = 'Pending';
                                                                break;
                                                            case 'approved':
                                                                $status_class = 'bg-success';
                                                                $status_text = 'Approved';
                                                                break;
                                                            case 'rejected':
                                                                $status_class = 'bg-danger';
                                                                $status_text = 'Rejected';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?> status-badge"><?= $status_text ?></span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <?php if ($request['status'] === 'pending'): ?>
                                                            <button 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewRequestModal"
                                                            data-request='<?= json_encode($request, JSON_HEX_APOS | JSON_UNESCAPED_SLASHES) ?>'>
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">Processed</span>
                                                        <?php endif; ?>
                                                    </td>

                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 calendar-container" id="calendarContainer">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">List of Approved Requests</h5>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleCalendar()">
                                                <i class="fas fa-times"></i><
                                            </button>
                                        </div>
                                        <div class="card-body" id="calendar">
                                            <!-- Calendar events will be dynamically inserted here -->
                                            <p class="text-muted small">Loading...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="viewRequestForm">
                <div class="modal-header">
                    <h5 class="modal-title">View Service Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="viewRequestId">
                    <input type="hidden" name="action" id="viewAction">

                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" id="viewClient" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Request Details</label>
                        <input type="text" id="viewDetails" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Request Description</label>
                        <textarea id="viewDescription" class="form-control" rows="2" readonly></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Request Date & Time</label>
                        <input type="text" id="viewDateTime" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Add Notes (Optional)</label>
                        <textarea class="form-control" name="admin_notes" id="viewNotes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="action" value="approve" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- Approval Modal -->
    <!-- <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Process Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approvalForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="modalRequestId">
                        <input type="hidden" name="action" id="modalAction">
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Add any notes or instructions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="modalSubmitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const viewModal = document.getElementById('viewRequestModal');
    viewModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const request = JSON.parse(button.getAttribute('data-request'));

        // Populate modal fields
        document.getElementById('viewRequestId').value = request.request_id;
        document.getElementById('viewClient').value = `${request.client_name} (${request.client_email})`;
        document.getElementById('viewDetails').value = request.request_details;
        document.getElementById('viewDescription').value = request.request_description;
        
        const date = new Date(request.request_date + 'T' + request.request_time);
        const formatted = date.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
        document.getElementById('viewDateTime').value = formatted;
    });
});
        function approveRequest(requestId) {
    console.log("Approving:", requestId);
    // AJAX or SweetAlert logic here
}

        // Get the current page URL
        // Store approved requests data
        const approvedRequests = <?= $approved_requests_json ?>;
        let calendarVisible = false;


        function approveRequest(requestId) {
            document.getElementById('modalRequestId').value = requestId;
            document.getElementById('modalAction').value = 'approve';
            document.getElementById('modalTitle').textContent = 'Approve Request';
            document.getElementById('modalSubmitBtn').className = 'btn btn-success';
            document.getElementById('modalSubmitBtn').textContent = 'Approve';
            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }
        
        function rejectRequest(requestId) {
            document.getElementById('modalRequestId').value = requestId;
            document.getElementById('modalAction').value = 'reject';
            document.getElementById('modalTitle').textContent = 'Reject Request';
            document.getElementById('modalSubmitBtn').className = 'btn btn-danger';
            document.getElementById('modalSubmitBtn').textContent = 'Reject';
            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }
        
        function toggleCalendar() {
            const calendarContainer = document.getElementById('calendarContainer');
            const tableContainer = document.getElementById('tableContainer');
            const toggleText = document.getElementById('toggleText');
            
            if (calendarVisible) {
                // Hide calendar
                calendarContainer.classList.add('calendar-hidden');
                tableContainer.classList.remove('col-md-8');
                tableContainer.classList.add('col-md-12');
                tableContainer.classList.add('table-expanded');
                toggleText.textContent = 'Show Approved';
                calendarVisible = false;
            } else {
                // Show calendar
                calendarContainer.classList.remove('calendar-hidden');
                tableContainer.classList.remove('col-md-12');
                tableContainer.classList.add('col-md-8');
                tableContainer.classList.remove('table-expanded');
                toggleText.textContent = 'Hide Approved';
                calendarVisible = true;
                initializeCalendar();
            }
        }
        
        // Simple calendar for approved requests
        function initializeCalendar() {
            const calendar = document.getElementById('calendar');
            
            if (!Array.isArray(approvedRequests)) {
                console.error('approvedRequests is not an array:', approvedRequests);
                calendar.innerHTML = '<p class="text-muted small">Error loading calendar data</p>';
                return;
            }
            
            let calendarHTML = '<div class="text-center mb-3">';
            calendarHTML += '<h6 class="text-muted">Approved Requests</h6>';
            
            if (approvedRequests.length === 0) {
                calendarHTML += '<p class="text-muted small">No approved requests</p>';
            } else {
                approvedRequests.forEach(request => {
                    const date = new Date(request.request_date);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric' 
                    });
                    
                    calendarHTML += `
                        <div class="calendar-event mb-1">
                            <i class="fas fa-check-circle me-1"></i>
                            ${request.request_details} - ${formattedDate}
                        </div>
                    `;
                });
            }
            
            calendarHTML += '</div>';
            calendar.innerHTML = calendarHTML;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar if it's visible by default
            if (calendarVisible) {
                initializeCalendar();
            }
        });

        
        document.addEventListener('DOMContentLoaded', function () {
        const viewRequestForm = document.getElementById('viewRequestForm');

        viewRequestForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent actual form submission

            const form = e.target;
            const action = form.querySelector('button[type=submit][clicked=true]').value;
            const actionText = action === 'approve' ? 'approve' : 'reject';

            Swal.fire({
                title: `Are you sure you want to ${actionText} this request?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Yes, ${actionText}`,
                cancelButtonText: 'Cancel',
                confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.querySelector('#viewAction').value = action;
                    form.submit();
                }
            });
        });

        // Track which button was clicked (approve or reject)
        const buttons = viewRequestForm.querySelectorAll('button[type=submit]');
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                buttons.forEach(b => b.removeAttribute('clicked'));
                btn.setAttribute('clicked', 'true');
            });
        });
    });

    </script>
</body>
</html> 