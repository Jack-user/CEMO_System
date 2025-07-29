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
  $raw_id = $_POST['schedule_id'];
  $numeric_id = preg_replace('/[^0-9]/', '', $raw_id);

  $event_name = trim($_POST['event_name']);
  $day = $_POST['day'];
  $time = $_POST['time'];
  $status = $_POST['status'];

  if (strpos($raw_id, 'maint_') === 0) {
  // Treat event_name as vehicle_name, day as m_date, etc.
  $stmt = $conn->prepare("UPDATE maintenance_table SET vehicle_name = ?, m_date = ?, m_time = ?, m_status = ? WHERE maintenance_id = ?");
  $stmt->bind_param("ssssi", $event_name, $day, $time, $status, $numeric_id);
} else {
    // Update schedule_table
    $stmt = $conn->prepare("UPDATE schedule_table SET event_name = ?, day = ?, time = ?, status = ? WHERE schedule_id = ?");
    $stmt->bind_param("ssssi", $event_name, $day, $time, $status, $numeric_id);
  }

  $success = $stmt->execute();
  $stmt->close();
  echo json_encode(['success' => $success]);
  exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit();
?>
