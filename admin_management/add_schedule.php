<?php
session_start();
header('Content-Type: application/json');

include '../includes/conn.php';

// Check login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$schedule_type = $_POST['schedule_type'] ?? '';

if ($schedule_type === 'Event') {
    $event_name = trim($_POST['event_name'] ?? '');
    $day = $_POST['day'] ?? '';
    $time = $_POST['time'] ?? '';
    $status = $_POST['status'] ?? 'Scheduled';

    if (empty($event_name) || empty($day) || empty($time)) {
        echo json_encode(['success' => false, 'error' => 'Missing required event fields']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO schedule_table (event_name, day, time, status) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $event_name, $day, $time, $status);
        $success = $stmt->execute();
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Event added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    }

} elseif ($schedule_type === 'Maintenance') {
    $maintenance_id = (int)($_POST['maintenance_id'] ?? 0); // This is waste_service_id
    $m_date = $_POST['m_date'] ?? '';
    $m_time = $_POST['m_time'] ?? '';
    $status = $_POST['maintenance_status'] ?? 'Scheduled';

    // Fetch vehicle name using waste_service_id
    $vehicleStmt = $conn->prepare("SELECT vehicle_name FROM waste_service_table WHERE waste_service_id = ?");
    $vehicleStmt->bind_param("i", $maintenance_id);
    $vehicleStmt->execute();
    $result = $vehicleStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid vehicle selected']);
        exit();
    }

    $vehicle = $result->fetch_assoc();
    $m_name = $vehicle['vehicle_name']; // Use actual name from DB
    $vehicleStmt->close();

    if (empty($m_date) || empty($m_time)) {
        echo json_encode(['success' => false, 'error' => 'Missing date or time']);
        exit();
    }

    // Check if maintenance already exists for this vehicle
    $checkStmt = $conn->prepare("SELECT maintenance_id FROM maintenance_table WHERE waste_service_id = ?");
    $checkStmt->bind_param("i", $maintenance_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update existing maintenance
        $row = $checkResult->fetch_assoc();
        $updateStmt = $conn->prepare("UPDATE maintenance_table SET m_name = ?, m_date = ?, m_time = ?, status = ? WHERE maintenance_id = ?");
        $updateStmt->bind_param("ssssi", $m_name, $m_date, $m_time, $status, $row['maintenance_id']);
        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Maintenance updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB error: ' . $updateStmt->error]);
        }
        $updateStmt->close();
    } else {
        // Insert new maintenance
        $stmt = $conn->prepare("
            INSERT INTO maintenance_table (m_name, m_date, m_time, status, waste_service_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssssi", $m_name, $m_date, $m_time, $status, $maintenance_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Maintenance scheduled successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'DB error: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        }
    }
    $checkStmt->close();

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid schedule type']);
}

$conn->close();
?>