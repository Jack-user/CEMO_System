<?php
session_start();
include '../includes/conn.php';
include '../includes/header.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// Mark notification as read if requested
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE client_notifications SET is_read = 1 WHERE id = ? AND client_id = ?");
    $stmt->execute([$id, $client_id]);
    header("Location: client_notifications.php");
    exit();
}

// Get client notifications
$stmt = $pdo->prepare("
    SELECT * FROM client_notifications 
    WHERE client_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$client_id]);
$notifications = $stmt->fetchAll();

// Get client's requests
$stmt = $pdo->prepare("
    SELECT * FROM client_requests 
    WHERE client_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$client_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Client - Notifications & Requests</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .notification-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        .notification-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .notification-card.unread {
            border-left-color: #007bff;
            background-color: #f8f9fa;
        }
        .notification-card.approved {
            border-left-color: #28a745;
        }
        .notification-card.rejected {
            border-left-color: #dc3545;
        }
        .request-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body class="g-sidenav-show bg-gray-200">
    <?php include '../sidebar/client_sidebar.php'; ?>
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg">
                        <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
                            <div style="background: linear-gradient(60deg, #667eea 0%, #764ba2 100%);" class="shadow-dark border-radius-lg pt-4 pb-3">
                                <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">
                                    <i class="fas fa-bell me-2"></i>Notifications & Requests
                                </h5>
                            </div>
                        </div>
                        
                        <div class="card-body px-0 pb-2">
                            <div class="row mx-4 mb-4">
                                <!-- Notifications Section -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-bell me-2"></i>Recent Notifications
                                                <?php 
                                                $unread_count = array_filter($notifications, function($n) { return !$n['is_read']; });
                                                if (count($unread_count) > 0): 
                                                ?>
                                                <span class="badge bg-danger ms-2"><?= count($unread_count) ?></span>
                                                <?php endif; ?>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($notifications)): ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-bell-slash text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-2">No notifications yet</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($notifications as $notification): ?>
                                                <div class="notification-card card mb-3 <?= $notification['is_read'] ? '' : 'unread' ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="card-title mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                                                <p class="card-text text-muted small mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                                                <small class="text-muted">
                                                                    <?= date('M d, Y H:i', strtotime($notification['created_at'])) ?>
                                                                </small>
                                                            </div>
                                                            <?php if (!$notification['is_read']): ?>
                                                            <button 
                                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center px-3 py-1 rounded view-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#notificationModal"
                                                            data-id="<?= $notification['id'] ?>"
                                                            data-title="<?= htmlspecialchars($notification['title'], ENT_QUOTES) ?>"
                                                            data-message="<?= htmlspecialchars($notification['message'], ENT_QUOTES) ?>"
                                                            data-date="<?= date('M d, Y H:i', strtotime($notification['created_at'])) ?>">
                                                            <span>View</span>
                                                            <i class="fas fa-check ms-1"></i>
                                                        </button>
                                                        <a href="client_notifications.php?mark_read=1&id=<?= $notification['id'] ?>" 
                                                        class="btn btn-sm btn-outline-secondary ms-2">
                                                        <i class="fas fa-check"></i> Mark as Read
                                                        </a>

                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Requests Section -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clipboard-list me-2"></i>My Requests
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($requests)): ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-clipboard text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-2">No requests submitted yet</p>
                                                    <a href="client_request.php" class="btn btn-primary">
                                                        <i class="fas fa-plus me-2"></i>Submit Request
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($requests as $request): ?>
                                                <div class="notification-card card mb-3 <?= $request['status'] ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="card-title mb-1"><?= htmlspecialchars($request['request_details']) ?></h6>
                                                                <p class="card-text text-muted small mb-2">
                                                                    <?= htmlspecialchars($request['request_description']) ?>
                                                                </p>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <small class="text-muted">
                                                                        Preferred: <?= date('M d, Y', strtotime($request['request_date'])) ?>
                                                                    </small>
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
                                                                    <span class="badge <?= $status_class ?> request-status"><?= $status_text ?></span>
                                                                </div>
                                                                <?php if ($request['admin_notes']): ?>
                                                                <div class="mt-2">
                                                                    <small class="text-muted">
                                                                        <strong>Admin Notes:</strong> <?= htmlspecialchars($request['admin_notes']) ?>
                                                                    </small>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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

    <!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 id="modal-title"></h6>
        <p id="modal-message" class="text-muted"></p>
        <small class="text-muted d-block mt-3">Created at: <span id="modal-date"></span></small>
      </div>
    </div>
  </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            // You can implement AJAX refresh here if needed
        }, 30000);

        // Handle notification modal
        document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('notificationModal');
    const titleEl = document.getElementById('modal-title');
    const messageEl = document.getElementById('modal-message');
    const dateEl = document.getElementById('modal-date');

    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const title = this.getAttribute('data-title');
            const message = this.getAttribute('data-message');
            const date = this.getAttribute('data-date');
            const id = this.getAttribute('data-id');

            // Set modal content
            titleEl.textContent = title;
            messageEl.textContent = message;
            dateEl.textContent = date;

            // Optional: Mark as read via AJAX
            fetch(`mark_read.php?id=${id}`, {
                method: 'GET'
            }).then(res => res.ok && console.log("Marked as read"));
        });
    });
});
    </script>
</body>
</html>