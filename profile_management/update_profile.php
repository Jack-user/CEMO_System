<?php
session_start();
require_once '../includes/conn.php';

if (!isset($_SESSION['client_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$client_id = $_SESSION['client_id'];

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$password = $_POST['password'] ?? '';

// Validate required fields
if ($first_name === '' || $last_name === '' || $email === '' || $barangay === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
    exit();
}
<<<<<<< HEAD
=======

// Check for existing email
$check = $conn->prepare("SELECT client_id FROM client_table WHERE email = ? AND client_id != ?");
$check->bind_param("si", $email, $client_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already in use.']);
    exit();
}
$check->close();

// If password is provided, hash it
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE client_table SET first_name = ?, last_name = ?, email = ?, contact = ?, barangay = ?, password = ? WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $contact, $barangay, $hashed_password, $client_id);
} else {
    $sql = "UPDATE client_table SET first_name = ?, last_name = ?, email = ?, contact = ?, barangay = ? WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $contact, $barangay, $client_id);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
}

$stmt->close();
$conn->close();
>>>>>>> a3f89c4fae7a2e130b8c906ac26a9b7aca7beb42
?>
