<?php
session_start();
include '../includes/conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $user_role = mysqli_real_escape_string($conn, $_POST['user_role']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $insert = "INSERT INTO admin_table 
        (first_name, last_name, user_role, email, gender, address, birth_date, contact, password)
        VALUES ('$first_name', '$last_name', '$user_role', '$email', '$gender', '$address', '$birth_date', '$contact', '$password')";

    if (mysqli_query($conn, $insert)) {
        $_SESSION['staff_success'] = "New staff successfully added!";
        header("Location: admin_role_list.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
