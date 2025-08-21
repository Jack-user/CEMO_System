<?php
session_start();
header('Content-Type: application/json');

include '../includes/conn.php';

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Determine action
if (isset($_POST['delete']) && isset($_POST['schedule_id'])) {
    // 🔴 DELETE SCHEDULE
    $schedule_id = $_POST['schedule_id'];

    if (strpos($schedule_id, 'event_') === 0) {
        $id = (int)str_replace('event_', '', $schedule_id);
        $stmt = $conn->prepare("DELETE FROM schedule_table WHERE schedule_id = ?");
        $stmt->bind_param("i", $id);
    } elseif (strpos($schedule_id, 'maint_') === 0) {
        $id = (int)str_replace('maint_', '', $schedule_id);
        $stmt = $conn->prepare("DELETE FROM maintenance_table WHERE maintenance_id = ?");
        $stmt->bind_param("i", $id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID format']);
        exit();
    }

    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $success]);
    exit();
}

if (isset($_POST['schedule_id'])) {
    // 🟡 EDIT SCHEDULE
    $raw_id = $_POST['schedule_id'];
    $event_name = trim($_POST['event_name'] ?? '');
    $day = $_POST['day'] ?? '';
    $time = $_POST['time'] ?? '';
    $status = $_POST['status'] ?? 'Scheduled';

    if (empty($event_name) || empty($day) || empty($time)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    if (strpos($raw_id, 'maint_') === 0) {
        $maintenance_id = (int)preg_replace('/[^0-9]/', '', $raw_id);

        // 🔒 Prevent double booking
        $checkStmt = $conn->prepare("
            SELECT maintenance_id FROM maintenance_table 
            WHERE waste_service_id = (
                SELECT waste_service_id FROM maintenance_table WHERE maintenance_id = ?
            )
            AND m_date = ? AND m_time = ? AND maintenance_id != ?
        ");
        $checkStmt->bind_param("isii", $maintenance_id, $day, $time, $maintenance_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'This vehicle is already scheduled for maintenance at this time.'
            ]);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();

        // ✅ Update maintenance_table
        $stmt = $conn->prepare("
            UPDATE maintenance_table 
            SET m_name = ?, m_date = ?, m_time = ?, status = ? 
            WHERE maintenance_id = ?
        ");
        $stmt->bind_param("ssssi", $event_name, $day, $time, $status, $maintenance_id);

    } elseif (strpos($raw_id, 'event_') === 0) {
        $schedule_id = (int)preg_replace('/[^0-9]/', '', $raw_id);

        // ✅ Update schedule_table
        $stmt = $conn->prepare("
            UPDATE schedule_table 
            SET event_name = ?, day = ?, time = ?, status = ? 
            WHERE schedule_id = ?
        ");
        $stmt->bind_param("ssssi", $event_name, $day, $time, $status, $schedule_id);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid schedule ID']);
        exit();
    }

    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
    exit();
}

if (isset($_POST['schedule_type'])) {
    // 🟢 ADD SCHEDULE
    $schedule_type = $_POST['schedule_type'];

    if ($schedule_type === 'Event') {
        $event_name = trim($_POST['event_name'] ?? '');
        $day = $_POST['day'] ?? '';
        $time = $_POST['time'] ?? '';
        $status = $_POST['status'] ?? 'Scheduled';

        if (empty($event_name) || empty($day) || empty($time)) {
            echo json_encode(['success' => false, 'message' => 'Missing required event fields']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO schedule_table (event_name, day, time, status) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $event_name, $day, $time, $status);
            $success = $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => $success, 'message' => $success ? 'Event added' : 'DB error']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Prepare failed']);
        }
        exit();
    }

    if ($schedule_type === 'Maintenance') {
        $waste_service_id = (int)($_POST['maintenance_id'] ?? 0);
        $m_date = $_POST['m_date'] ?? '';
        $m_time = $_POST['m_time'] ?? '';
        $status = $_POST['maintenance_status'] ?? 'Scheduled';

        if (empty($m_date) || empty($m_time) || $waste_service_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing required maintenance fields']);
            exit();
        }

        // Get vehicle name from ID
        $vehicleStmt = $conn->prepare("SELECT vehicle_name FROM waste_service_table WHERE waste_service_id = ?");
        $vehicleStmt->bind_param("i", $waste_service_id);
        $vehicleStmt->execute();
        $result = $vehicleStmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid vehicle selected']);
            $vehicleStmt->close();
            exit();
        }
        $vehicle = $result->fetch_assoc();
        $m_name = $vehicle['vehicle_name'];
        $vehicleStmt->close();

        // Insert
        $stmt = $conn->prepare("
            INSERT INTO maintenance_table (m_name, m_date, m_time, status, waste_service_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssssi", $m_name, $m_date, $m_time, $status, $waste_service_id);
            $success = $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => $success, 'message' => $success ? 'Maintenance added' : 'DB error']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insert prepare failed']);
        }
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Invalid schedule type']);
    exit();
}

// Default response
echo json_encode(['success' => false, 'message' => 'No valid action']);
exit();
?>