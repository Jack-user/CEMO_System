<?php
session_start();
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all input values safely
    $driver_id    = $_POST['driver_id'];
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $contact      = trim($_POST['contact']);
    $address      = trim($_POST['address']);
    $age          = intval($_POST['age']);
    $gender       = trim($_POST['gender']);
    // $birth_date   = $_POST['birth_date'];
    $license_no   = trim($_POST['license_no']);
    $password     = $_POST['password']; // optional

    // Validate required fields (you can add more validation if needed)
    if (empty($driver_id) || empty($first_name) || empty($last_name) || empty($contact) || empty($address) || empty($age) || empty($gender) || empty($license_no)) {
        $_SESSION['driver_error'] = "Please fill in all required fields.";
        header("Location: admin_role_list.php");
        exit();
    }

    // Update query with or without password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE driver_table SET 
            first_name = ?, 
            last_name = ?, 
            contact = ?, 
            address = ?, 
            age = ?, 
            gender = ?, 
            license_no = ?, 
            password = ? 
            WHERE driver_id = ?");
        $stmt->bind_param("ssssissi", $first_name, $last_name, $contact, $address, $age, $gender, $license_no, $hashed_password, $driver_id);
    } else {
        $stmt = $conn->prepare("UPDATE driver_table SET 
            first_name = ?, 
            last_name = ?, 
            contact = ?, 
            address = ?, 
            age = ?, 
            gender = ?, 
            license_no = ? 
            WHERE driver_id = ?");
        $stmt->bind_param("ssssissi", $first_name, $last_name, $contact, $address, $age, $gender, $license_no, $driver_id);
    }

    if ($stmt->execute()) {
        $_SESSION['driver_success'] = "Driver updated successfully!";
    } else {
        $_SESSION['driver_error'] = "Update failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: admin_role_list.php");
    exit();
} else {
    $_SESSION['driver_error'] = "Invalid request.";
    header("Location: admin_role_list.php");
    exit();
}
