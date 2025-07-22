<?php
include '../includes/conn.php';

header('Content-Type: application/json');

$sql = "SELECT latitude, longitude FROM gps_location ORDER BY location_id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'latitude' => floatval($row['latitude']),
        'longitude' => floatval($row['longitude'])
    ]);
} else {
    echo json_encode(['latitude' => null, 'longitude' => null]);
}
?>
