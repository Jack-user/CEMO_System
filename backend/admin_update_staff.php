<?php
session_start();
include '../includes/conn.php';

// Check if the user is logged in (optional, depends on your security model)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message'] = "Access denied.";
    header("Location: ../admin_management/admin_role_list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = intval($_POST['admin_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $user_role = trim($_POST['user_role'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    $errors = [];
    if (empty($admin_id) || empty($first_name) || empty($last_name) || empty($user_role) || empty($email) || empty($contact) || empty($address)) {
        $errors[] = "Required fields are missing.";
    }

    // Email format check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Invalid Gmail address.";
    }

    // Contact format check
    if (!preg_match('/^[0-9]{11}$/', $contact)) {
         $errors[] = "Contact must be 11 digits.";
    }


    if (empty($errors)) {
        // Start building the query
        $sql = "UPDATE admin_table SET first_name = ?, last_name = ?, user_role = ?, email = ?, contact = ?, address = ?";
        $types = "ssssss"; // Types for bind_param
        $params = [&$first_name, &$last_name, &$user_role, &$email, &$contact, &$address]; // Parameters array (references needed)

        // Handle password update
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $types .= "s";
            $params[] = &$hashedPassword;
        }

        $sql .= " WHERE admin_id = ?";
        $types .= "i";
        $params[] = &$admin_id;

        // Prepare statement
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            // Bind parameters dynamically
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['staff_success'] = "Staff updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating staff: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error_message'] = "Error preparing statement: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = implode(" ", $errors); // Combine errors
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: ../admin_management/admin_role_list.php");
exit();
?>