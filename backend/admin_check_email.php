<?php
include '../includes/conn.php';

if (isset($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);

    $adminCheck = mysqli_query($conn, "SELECT * FROM admin_table WHERE email = '$email'");
    $clientCheck = mysqli_query($conn, "SELECT * FROM client_table WHERE email = '$email'");

    if (mysqli_num_rows($adminCheck) > 0 || mysqli_num_rows($clientCheck) > 0) {
        echo "taken";
    } else {
        echo "available";
    }
}
?>
