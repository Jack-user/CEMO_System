<?php
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['client_id'];
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    
    // Get current password if not changed
    $password = !empty($_POST['password']) 
        ? password_hash($_POST['password'], PASSWORD_DEFAULT)
        : mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM client_table WHERE client_id='$id'"))['password'];

    // Real-time uniqueness check
    $checkEmail = mysqli_query($conn, "SELECT client_id FROM client_table WHERE email='$email' AND client_id != '$id'");
    $checkContact = mysqli_query($conn, "SELECT client_id FROM client_table WHERE contact='$contact' AND client_id != '$id'");
    
    if (mysqli_num_rows($checkEmail) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }

    if (mysqli_num_rows($checkContact) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Contact already exists']);
        exit;
    }

    $update = "UPDATE client_table SET 
        first_name='$first_name',
        last_name='$last_name',
        email='$email',
        contact='$contact',
        barangay='$barangay',
        password='$password'
        WHERE client_id='$id'";

    if (mysqli_query($conn, $update)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . mysqli_error($conn)]);
    }
    exit;
}
?>
