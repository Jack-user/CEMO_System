<?php
session_start();
include '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $vehicleName = $_POST['vehicle_name'];
  $day = $_POST['day'];
  $status = $_POST['status'];

  // Optional: sanitize inputs

  // Get waste_service_id based on vehicle name (or insert logic if needed)
  $stmt = $conn->prepare("SELECT waste_service_id FROM waste_service_table WHERE vehicle_name = ?");
  $stmt->bind_param("s", $vehicleName);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $wasteServiceId = $row['waste_service_id'];

    $insert = $conn->prepare("INSERT INTO schedule_table (waste_service_id, day, status) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $wasteServiceId, $day, $status);
    $insert->execute();
  }

  $stmt->close();
  $conn->close();
  header("Location: waste_service_sched.php"); // redirect back to calendar
  exit();
}

// If you want to support AJAX, you can add:
if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
  echo json_encode(['success' => true]);
  exit();
}
?>
