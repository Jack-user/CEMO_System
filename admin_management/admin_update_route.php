<?php
session_start();
require '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'];
    $end_point = $_POST['end_point'];

    $stmt = $conn->prepare("UPDATE route_table SET end_point = ? WHERE route_id = ?");
    $stmt->bind_param("si", $end_point, $route_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Route updated successfully.";
    } else {
        $_SESSION['msg'] = "Error updating route.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
