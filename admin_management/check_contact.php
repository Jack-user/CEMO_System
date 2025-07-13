<?php
include '../includes/conn.php';

if (isset($_GET['contact'])) {
    $contact = mysqli_real_escape_string($conn, $_GET['contact']);

    $adminCheck = mysqli_query($conn, "SELECT * FROM admin_table WHERE contact = '$contact'");
    $clientCheck = mysqli_query($conn, "SELECT * FROM client_table WHERE contact = '$contact'");

    if (mysqli_num_rows($adminCheck) > 0 || mysqli_num_rows($clientCheck) > 0) {
        echo "taken";
    } else {
        echo "available";
    }
}
?>
