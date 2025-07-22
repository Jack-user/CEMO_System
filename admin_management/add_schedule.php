<?php
session_start();
include '../includes/conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $schedule_type = $_POST["schedule_type"];
  $status = $schedule_type === 'Event' ? $_POST["status"] : $_POST["maintenance_status"];

  if ($schedule_type === 'Event') {
    $event_name = trim($_POST["event_name"]);
    $day = $_POST["day"];
    $time = $_POST["time"];

    if (!empty($event_name) && !empty($day) && !empty($time) && !empty($status)) {
      $stmt = $conn->prepare("INSERT INTO schedule_table (event_name, day, time, status) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $event_name, $day, $time, $status);

      if ($stmt->execute()) {
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Please fill in all event fields.']);
    }

  } elseif ($schedule_type === 'Maintenance') {
    $m_name = trim($_POST["maintenance_name"]);
    $m_date = $_POST["m_date"];
    $m_time = $_POST["m_time"];

    if (!empty($m_name) && !empty($m_date) && !empty($m_time) && !empty($status)) {
      $stmt = $conn->prepare("INSERT INTO maintenance_table (m_name, m_date, m_time, m_status) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $m_name, $m_date, $m_time, $status);

      if ($stmt->execute()) {
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Please fill in all maintenance fields.']);
    }

  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid schedule type.']);
  }
}
?>
