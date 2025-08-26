<?php
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $vehicle = $_POST['vehicle_name'];
  $day = $_POST['day'];
  $status = $_POST['status'];

  // Find waste_service_id by vehicle name
  $getVehicle = $conn->prepare("SELECT waste_service_id FROM waste_service_table WHERE vehicle_name = ?");
  $getVehicle->bind_param("s", $vehicle);
  $getVehicle->execute();a
  $getVehicle->bind_result($vehicleId);
  $getVehicle->fetch();
  $getVehicle->close();

  if ($vehicleId) {
    $stmt = $conn->prepare("INSERT INTO schedule_table (waste_service_id, day, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $vehicleId, $day, $status);
    $stmt->execute();
    $stmt->close();
  }

  header("Location: vehicle_assignment.php");
  exit();
}
?>
