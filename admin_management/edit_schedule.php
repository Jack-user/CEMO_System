<?php
session_start();
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

// Delete Schedule
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete']) && isset($_POST['schedule_id'])) {
  $schedule_id = $_POST['schedule_id'];

  // 🟢 Added support for maintenance schedule deletion
  if (strpos($schedule_id, 'event_') === 0) {
    $id = str_replace('event_', '', $schedule_id);
    $stmt = $conn->prepare("DELETE FROM schedule_table WHERE schedule_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
  } elseif (strpos($schedule_id, 'maint_') === 0) {
    $id = str_replace('maint_', '', $schedule_id);
    $stmt = $conn->prepare("DELETE FROM maintenance_table WHERE maintenance_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
  }
}

// Update Schedule
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['schedule_id'])) {
  $schedule_id = $_POST['schedule_id'];
  $event_name = trim($_POST['event_name']);
  $day = $_POST['day'];
  $time = $_POST['time'];
  $status = $_POST['status'];

  $stmt = $conn->prepare("UPDATE schedule_table SET event_name = ?, day = ?, time = ?, status = ? WHERE schedule_id = ?");
  $stmt->bind_param("ssssi", $event_name, $day, $time, $status, $schedule_id);
  $success = $stmt->execute();
  $stmt->close();

  echo json_encode(['success' => $success]);
  exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();
?>
