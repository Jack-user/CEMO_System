<?php
session_start();
include '../includes/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Delete
  if (isset($_POST['delete']) && $_POST['delete'] == '1') {
    $schedule_id = intval($_POST['schedule_id']);
    $stmt = $conn->prepare("DELETE FROM schedule_table WHERE schedule_id = ?");
    $stmt->bind_param("i", $schedule_id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
  }

  // Edit
  $schedule_id = intval($_POST['schedule_id']);
  $vehicle_name = $_POST['vehicle_name'];
  $day = $_POST['day'];
  $status = $_POST['status'];

  // Get waste_service_id
  $stmt = $conn->prepare("SELECT waste_service_id FROM waste_service_table WHERE vehicle_name = ?");
  $stmt->bind_param("s", $vehicle_name);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $waste_service_id = $row['waste_service_id'];
    $stmt->close();

    $update = $conn->prepare("UPDATE schedule_table SET waste_service_id = ?, day = ?, status = ? WHERE schedule_id = ?");
    $update->bind_param("issi", $waste_service_id, $day, $status, $schedule_id);
    $success = $update->execute();
    $update->close();
    echo json_encode(['success' => $success]);
    exit();
  }
  echo json_encode(['success' => false]);
  exit();
}
echo json_encode(['success' => false]);
?>
