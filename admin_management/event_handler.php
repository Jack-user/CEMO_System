<?php
include '../includes/conn.php';

if (isset($_POST['delete']) && $_POST['id']) {
  $id = $_POST['id'];
  $stmt = $conn->prepare("DELETE FROM schedule_table WHERE schedule_id = ?");
  $stmt->bind_param("i", $id);
  echo json_encode(['success' => $stmt->execute()]);
  exit();
}

$id     = $_POST['eventId'];
$title  = $_POST['title'];
$date   = $_POST['date'];
$status = $_POST['status'];

// Dummy waste_service_id (in production, get from title or a form dropdown)
$vehicle_name = explode(' - ', $title)[0];
$result = $conn->query("SELECT waste_service_id FROM waste_service_table WHERE vehicle_name = '$vehicle_name'");
$row = $result->fetch_assoc();
$waste_service_id = $row ? $row['waste_service_id'] : 1;

if ($id) {
  $stmt = $conn->prepare("UPDATE schedule_table SET day=?, status=? WHERE schedule_id=?");
  $stmt->bind_param("ssi", $date, $status, $id);
} else {
  $stmt = $conn->prepare("INSERT INTO schedule_table (waste_service_id, day, status) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $waste_service_id, $date, $status);
}

echo json_encode(['success' => $stmt->execute()]);
