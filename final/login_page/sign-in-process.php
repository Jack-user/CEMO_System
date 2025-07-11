<?php
session_start();
require_once '../includes/conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $_SESSION['login_attempt_email'] = $email; // Store email for reset link

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "empty_fields";
        header("Location: sign-in.php");
        exit;
    }

    // Fetch Admin details from database
    $stmt = $pdo->prepare("SELECT * FROM admin_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $adminUser = $stmt->fetch();

    // Fetch Client details from database
    $stmt = $pdo->prepare("SELECT * FROM client_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $clientUser = $stmt->fetch();

    // Check if the user exists in either table
    if (!$adminUser && !$clientUser) {
        $_SESSION['login_error'] = "invalid_user";
        header("Location: sign-in.php");
        exit;
    }

    // Verify Admin Login
    if ($adminUser && password_verify($password, $adminUser['password'])) {
        // ✅ Store Admin Session
        $_SESSION['user_role'] = explode(',', $adminUser['user_role']); // Convert role string to array if needed
        $_SESSION['email'] = $adminUser['email'];
        $_SESSION['admin_id'] = $adminUser['admin_id'];

        // ✅ Login Successful
        unset($_SESSION['login_error'], $_SESSION['login_attempt_email']);
        header("Location: ../dashboard_management/admin_dashboard.php");
        exit;
    }

    // Verify Client Login
    if ($clientUser && password_verify($password, $clientUser['password'])) {
        // ✅ Store Client Session
        $_SESSION['client_id'] = $clientUser['client_id'];
        $_SESSION['first_name'] = $clientUser['name']; // For customer
        $_SESSION['email'] = $clientUser['email'];

        // ✅ Login Successful
        unset($_SESSION['login_error'], $_SESSION['login_attempt_email']);
        header('location: ../admin_management/admin_map.php');
        exit;
    }

    // If no valid credentials were found
    $_SESSION['login_error'] = "wrong_password";
    header("Location: sign-in.php");
    exit;
}
?>