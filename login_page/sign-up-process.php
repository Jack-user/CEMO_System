<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/conn.php'; // Ensure this file properly initializes $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve first_name and last_name separately
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $contact = trim($_POST['contact']);
    $barangay = trim($_POST['barangay']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Combine into full_name
    // $full_name = $first_name . ' ' . $last_name;

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($contact)) {
        $_SESSION['signup_error'] = "empty_fields";
        header("Location: sign-up.php");
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT email FROM client_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $_SESSION['signup_error'] = "email_exists";
        header("Location: sign-up.php");
        exit;
    }

    // Check if contact number already exists
    $stmt = $pdo->prepare("SELECT contact FROM client_table WHERE contact = :contact");
    $stmt->bindParam(':contact', $contact);
    $stmt->execute();

    if ($stmt->fetch()) {
        $_SESSION['signup_error'] = "contact_exists";
        header("Location: sign-up.php");
        exit;
    }


    // ✅ Secure password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Update the INSERT statement to store full_name
    $stmt = $pdo->prepare("INSERT INTO client_table (first_name, last_name, contact, barangay, email, password) VALUES (:first_name, :last_name, :contact, :barangay,:email, :password)");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':barangay', $barangay);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Account created successfully!";
        header("Location: sign-in.php?status=success");
        exit;
    } else {
        $_SESSION['signup_error'] = "signup_failed";
        header("Location: sign-up.php");
        exit;
    }
}

// Error Messages
$messages = [
    "empty_fields" => "Please fill in all fields.",
    "email_exists" => "This email is already registered.",
    "contact_exists" => "The contact is already registered.",
    "signup_failed" => "Something went wrong. Please try again."
];

$status = $_GET['status'] ?? null;
?>

<!-- Toast Notifications -->
<?php if ($status && isset($messages[$status])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastMessage" class="toast align-items-center text-white <?= ($status === 'success') ? 'bg-success' : 'bg-danger'; ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($messages[$status]) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>
