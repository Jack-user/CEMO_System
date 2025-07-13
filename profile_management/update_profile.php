<?php
session_start();
require_once '../includes/conn.php';

// Make sure the user is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

$clientId = $_SESSION['client_id']; // ✅ Use the session, not $_POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    if (empty($firstName) || empty($lastName) || empty($email)) {
        die("Please fill in all fields.");
    }

    $stmt = $pdo->prepare("UPDATE client_table SET first_name = ?, last_name = ?, email = ? WHERE client_id = ?");
    $stmt->execute([$firstName, $lastName, $email, $clientId]);

    $_SESSION['msg'] = "Profile updated successfully!";
    header("Location: ../client_management/client_profile.php");
    exit();
}
?>