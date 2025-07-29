<?php
session_start();
include '../includes/conn.php';

// Check if the user is logged in (optional, depends on your security model)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['driver_error'] = "Access denied.";
    header("Location: admin_role_list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = intval($_POST['driver_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    // $birth_date = trim($_POST['birth_date'] ?? ''); // Assuming birth_date isn't used
    $license_no = trim($_POST['license_no'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    $errors = [];
    if (empty($driver_id) || empty($first_name) || empty($last_name) || empty($contact) || empty($address) || empty($gender) || empty($age) || empty($license_no)) {
        $errors[] = "Required fields are missing.";
    }

    // Contact format check
    if (!preg_match('/^[0-9]{11}$/', $contact)) {
         $errors[] = "Contact must be 11 digits.";
    }

    if (empty($errors)) {
        // Start building the query
        $sql = "UPDATE driver_table SET first_name = ?, last_name = ?, contact = ?, address = ?, gender = ?, age = ?, license_no = ?";
        $types = "ssssssi"; // Types for bind_param
        $params = [&$first_name, &$last_name, &$contact, &$address, &$gender, &$age, &$license_no]; // Parameters array (references needed)

        // Handle password update
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $types .= "s";
            $params[] = &$hashedPassword;
        }

        $sql .= " WHERE driver_id = ?";
        $types .= "i";
        $params[] = &$driver_id;

        // Prepare statement
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            // Bind parameters dynamically
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['driver_success'] = "Driver updated successfully.";
            } else {
                $_SESSION['driver_error'] = "Error updating driver: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['driver_error'] = "Error preparing statement: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['driver_error'] = implode(" ", $errors); // Combine errors
    }
} else {
    $_SESSION['driver_error'] = "Invalid request method.";
}

header("Location: admin_role_list.php");
exit();
?>