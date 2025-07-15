<?php
include '../includes/conn.php';
$data = json_decode(file_get_contents('php://input'), true);

$vehicle = $conn->real_escape_string($data['vehicle']);
$date = $conn->real_escape_string($data['date']);
$status = $conn->real_escape_string($data['status']);

// Find schedule by vehicle and date (adjust query based on your table structure)
$sql = "UPDATE waste_collection_table s
        LEFT JOIN waste_service_table w ON s.waste_service_id = w.waste_service_id
        SET s.status = '$status'
        WHERE w.vehicle_name = '$vehicle' AND s.day = '$date'";

$conn->query($sql);
echo json_encode(['success' => true]);
