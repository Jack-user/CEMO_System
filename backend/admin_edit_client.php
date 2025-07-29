<?php
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['client_id'];
    $first = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $password = $_POST['password'];

    $update_query = "UPDATE client_table SET 
        first_name = '$first', 
        last_name = '$last', 
        email = '$email', 
        contact = '$contact'";

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update_query .= ", password = '$hashed'";
    }

    $update_query .= " WHERE client_id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}
?>
