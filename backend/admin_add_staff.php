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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $user_role = trim($_POST['user_role'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation (you might want more robust validation)
    $errors = [];
    if (empty($first_name) || empty($last_name) || empty($user_role) || empty($gender) || empty($email) || empty($contact) || empty($address) || empty($birth_date) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // Email format check (basic)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Invalid Gmail address.";
    }

    // Contact format check
    if (!preg_match('/^[0-9]{11}$/', $contact)) {
         $errors[] = "Contact must be 11 digits.";
    }

    // Check for duplicate email (redundant check, but good practice)
    $checkEmailStmt = mysqli_prepare($conn, "SELECT admin_id FROM admin_table WHERE email = ?");
    mysqli_stmt_bind_param($checkEmailStmt, "s", $email);
    mysqli_stmt_execute($checkEmailStmt);
    $result = mysqli_stmt_get_result($checkEmailStmt);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email already exists.";
    }
    mysqli_stmt_close($checkEmailStmt);

    // Check for duplicate contact (redundant check, but good practice)
    $checkContactStmt = mysqli_prepare($conn, "SELECT admin_id FROM admin_table WHERE contact = ?");
    mysqli_stmt_bind_param($checkContactStmt, "s", $contact);
    mysqli_stmt_execute($checkContactStmt);
    $result = mysqli_stmt_get_result($checkContactStmt);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Contact number already exists.";
    }
    mysqli_stmt_close($checkContactStmt);


    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO admin_table (first_name, last_name, user_role, gender, email, contact, address, birth_date, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssssss", $first_name, $last_name, $user_role, $gender, $email, $contact, $address, $birth_date, $hashedPassword);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['staff_success'] = "Staff added successfully.";
        } else {
            $_SESSION['error_message'] = "Error adding staff: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = implode(" ", $errors); // Combine errors
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: ../admin_management/admin_role_list.php");
exit();
?>