<?php
include '../includes/conn.php';

header('Content-Type: application/json');

$sql = "SELECT latitude, longitude, location_id FROM gps_location ORDER BY location_id DESC LIMIT 5";
$result = $conn->query($sql);
$gps_points = [];
$technical_warning = false;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $gps_points[] = [
            'latitude' => floatval($row['latitude']),
            'longitude' => floatval($row['longitude']),
            'location_id' => intval($row['location_id'])
        ];
    }
    
    // Frontend will handle the time-based warning logic
    // Backend just provides the GPS data
    $technical_warning = false;
    
    echo json_encode([
        'gps_points' => $gps_points,
        'technical_warning' => $technical_warning,
        'latest_coords' => !empty($gps_points) ? $gps_points[0] : null,
        'coords_count' => count($gps_points)
    ]);
} else {
    echo json_encode([
        'gps_points' => [],
        'technical_warning' => false,
        'latest_coords' => null,
        'coords_count' => 0
    ]);
}
?>