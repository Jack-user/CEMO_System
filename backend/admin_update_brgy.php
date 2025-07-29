<?php
session_start();
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brgy'])) {
    $brgy_id = $_POST['brgy_id'];
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $facebook_link = mysqli_real_escape_string($conn, $_POST['facebook_link']);
    $link_text = mysqli_real_escape_string($conn, $_POST['link_text']);

    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'message' => 'Invalid latitude or longitude!'
        ];
    } else {
        $update_query = "UPDATE barangays_table 
                         SET barangay = '$barangay', latitude = '$latitude', longitude = '$longitude', 
                             facebook_link = '$facebook_link', link_text = '$link_text'
                         WHERE brgy_id = '$brgy_id'";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['swal'] = [
                'type' => 'success',
                'message' => 'Barangay details updated successfully!'
            ];
        } else {
            $_SESSION['swal'] = [
                'type' => 'error',
                'message' => 'Error updating: ' . mysqli_error($conn)
            ];
        }
    }

    header("Location: ../admin_management/admin_barangay_list.php");
    exit;
}
?>
