<?php
// get_recent_logs.php

// Optional: Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Adjust path to conn.php based on location
include '../includes/conn.php'; // Assuming this file is in /admin_management/

// Check connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Fetch last 5 completed events from log
$sql = "SELECT * FROM past_events_log ORDER BY date DESC, time DESC LIMIT 5";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit();
}

$logs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
} else {
    // No rows found — not an error, just empty
    $logs = [];
}

// Return JSON
echo json_encode($logs);
?>