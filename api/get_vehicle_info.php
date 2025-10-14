<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/conn.php';

function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

try {
    // 1) Get the active vehicle, route and driver (assume first configured for now)
    $sql = "SELECT w.waste_service_id, w.vehicle_name, w.vehicle_capacity, w.plate_no,
                    d.first_name, d.last_name,
                    r.start_point, r.end_point
            FROM waste_service_table w
            LEFT JOIN driver_table d ON d.driver_id = w.driver_id
            LEFT JOIN route_table r ON r.route_id = w.route_id
            ORDER BY w.waste_service_id ASC
            LIMIT 1";
    $stmt = $pdo->query($sql);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        echo json_encode(['success' => false, 'error' => 'No vehicle configured']);
        exit;
    }

    $vehicleName = $vehicle['vehicle_name'] ?? 'Vehicle';
    $driverName = trim(($vehicle['first_name'] ?? '') . ' ' . ($vehicle['last_name'] ?? ''));
    $startPointName = $vehicle['start_point'] ?? 'Bago City Hall';
    $endPointName = $vehicle['end_point'] ?? '';

    // 2) Latest GPS
    $gpsStmt = $pdo->query("SELECT latitude, longitude FROM gps_location ORDER BY timestamp DESC, location_id DESC LIMIT 1");
    $gps = $gpsStmt->fetch();
    $currentLat = isset($gps['latitude']) ? (float)$gps['latitude'] : null;
    $currentLng = isset($gps['longitude']) ? (float)$gps['longitude'] : null;

    // Default current location text
    $currentLocation = 'Unknown';

    // 3) Resolve coordinates for start and end
    // Bago City Hall default
    $startLat = 10.538274; $startLng = 122.835230;

    if (strtolower($startPointName) !== 'bago city hall') {
        $sp = $pdo->prepare("SELECT latitude, longitude FROM barangays_table WHERE barangay = ? LIMIT 1");
        $sp->execute([$startPointName]);
        if ($row = $sp->fetch()) {
            $startLat = (float)$row['latitude'];
            $startLng = (float)$row['longitude'];
        }
    }

    $endLat = null; $endLng = null;
    if (!empty($endPointName)) {
        $ep = $pdo->prepare("SELECT latitude, longitude FROM barangays_table WHERE barangay = ? LIMIT 1");
        $ep->execute([$endPointName]);
        if ($row = $ep->fetch()) {
            $endLat = (float)$row['latitude'];
            $endLng = (float)$row['longitude'];
        }
    }

    // 4) Determine nearest barangay to current GPS as the human-readable location
    $status = 'On going';
    $nearestBarangay = null;
    if ($currentLat !== null && $currentLng !== null) {
        $bStmt = $pdo->query("SELECT barangay, latitude, longitude FROM barangays_table WHERE city = 'Bago City'");
        $minDist = PHP_FLOAT_MAX;
        while ($b = $bStmt->fetch()) {
            $bLat = (float)$b['latitude'];
            $bLng = (float)$b['longitude'];
            $dist = haversineDistanceKm($currentLat, $currentLng, $bLat, $bLng);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearestBarangay = $b['barangay'];
            }
        }
        if ($nearestBarangay) {
            $currentLocation = $nearestBarangay;
        } else {
            $currentLocation = number_format($currentLat, 5) . ", " . number_format($currentLng, 5);
        }
    }

    // 5) Derive status (four states)
    // Fetch latest sensor sample (count and timestamp)
    $sensorLatest = $pdo->query("SELECT count, timestamp FROM sensor ORDER BY timestamp DESC, sensor_id DESC LIMIT 1")->fetch();
    $latestCount = isset($sensorLatest['count']) ? (int)$sensorLatest['count'] : 0;
    $latestCountTs = isset($sensorLatest['timestamp']) ? strtotime($sensorLatest['timestamp']) : null;

    // Consider sensor 'active' if recent (last 5 minutes)
    $nowTs = time();
    $sensorActive = ($latestCountTs !== null && ($nowTs - $latestCountTs) <= 300);
    $isCollecting = $sensorActive && $latestCount > 0;
    $nearEndPoint = (!empty($endPointName) && $nearestBarangay === $endPointName);
    $insideAnyBarangay = !empty($nearestBarangay);

    if ($nearEndPoint && !$isCollecting) {
        $status = 'Route Accomplished';
    } elseif ($isCollecting) {
        $status = 'Collecting';
    } elseif ($insideAnyBarangay) {
        $status = 'Collected';
    } else {
        $status = 'Ongoing';
    }

    // 6) Get sensor data for capacity calculation
    $maxCapacity = 1000; // Set maximum capacity for testing
    $currentCount = 0;
    $capacityPercent = 0; // Default to 0 if no sensor data
    
    // Get the latest sensor count
    $sensorStmt = $pdo->query("SELECT count FROM sensor ORDER BY timestamp DESC, sensor_id DESC LIMIT 1");
    $sensorData = $sensorStmt->fetch();
    
    if ($sensorData) {
        $currentCount = (int)$sensorData['count'];
        // Calculate capacity percentage based on sensor count
        $capacityPercent = (int) round(min(1000, max(0, ($currentCount / $maxCapacity) * 100)));
    }
    // If no sensor data, capacity remains 0%

    // Determine capacity status
    $capacityStatus = 'normal';
    if ($capacityPercent >= 1000) {
        $capacityStatus = 'full';
    } elseif ($capacityPercent >= 80) {
        $capacityStatus = 'warning';
    }

    echo json_encode([
        'success' => true,
        'vehicle_name' => $vehicleName,
        'driver_name' => $driverName,
        'status' => $status,
        'current_location' => $currentLocation,
        'capacity_percent' => $capacityPercent,
        'capacity_count' => $currentCount,
        'capacity_max' => $maxCapacity,
        'capacity_status' => $capacityStatus,
        'start_point' => $startPointName,
        'end_point' => $endPointName,
        'gps' => [ 'latitude' => $currentLat, 'longitude' => $currentLng ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>


