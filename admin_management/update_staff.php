<?php
include '../includes/conn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['admin_id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $role = $_POST['user_role'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE admin_table SET first_name='$fname', last_name='$lname', user_role='$role', email='$email', contact='$contact', address='$address', password='$hashed' WHERE admin_id='$id'";
    } else {
        $query = "UPDATE admin_table SET first_name='$fname', last_name='$lname', user_role='$role', email='$email', contact='$contact', address='$address' WHERE admin_id='$id'";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['staff_success'] = "Staff updated successfully.";
    }

    header("Location: admin_role_list.php");
    exit();
}
?>
