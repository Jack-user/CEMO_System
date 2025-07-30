<?php
include '../includes/conn.php';

header('Content-Type: application/json');


$sql = "SELECT latitude, longitude, location_id, timestamp FROM gps_location ORDER BY location_id DESC LIMIT 5";
$result = $conn->query($sql);
$gps_points = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $gps_points[] = [
            'latitude' => floatval($row['latitude']),
            'longitude' => floatval($row['longitude']),
            'location_id' => intval($row['location_id']),
            'timestamp' => isset($row['timestamp']) ? $row['timestamp'] : null
        ];
    }
    echo json_encode(['gps_points' => $gps_points]);
} else {
    echo json_encode(['gps_points' => []]);
}
?>