<?php
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "cemo_db");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]));
}

// ---- Input parsing: JSON first, then POST/GET fallback (backward-compatible) ----
$raw = file_get_contents("php://input");
$asJson = json_decode($raw, true);

$sensor_id   = isset($asJson['sensor_id']) ? (int)$asJson['sensor_id'] : (isset($_POST['sensor_id']) ? (int)$_POST['sensor_id'] : (isset($_GET['sensor_id']) ? (int)$_GET['sensor_id'] : 0));
$count       = isset($asJson['count']) ? (int)$asJson['count'] : (isset($_POST['count']) ? (int)$_POST['count'] : (isset($_GET['count']) ? (int)$_GET['count'] : 0));
$brgy_id     = isset($asJson['brgy_id']) ? (int)$asJson['brgy_id'] : (isset($_POST['brgy_id']) ? (int)$_POST['brgy_id'] : (isset($_GET['brgy_id']) ? (int)$_GET['brgy_id'] : 0));
$location_id = isset($asJson['location_id']) ? (int)$asJson['location_id'] : (isset($_POST['location_id']) ? (int)$_POST['location_id'] : (isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0));

// Optional fields (floats)
$distance    = isset($asJson['distance']) ? (float)$asJson['distance'] : (isset($_POST['distance']) ? (float)$_POST['distance'] : (isset($_GET['distance']) ? (float)$_GET['distance'] : null));
$latitude    = isset($asJson['latitude']) ? (float)$asJson['latitude'] : (isset($_POST['latitude']) ? (float)$_POST['latitude'] : (isset($_GET['latitude']) ? (float)$_GET['latitude'] : null));
$longitude   = isset($asJson['longitude']) ? (float)$asJson['longitude'] : (isset($_POST['longitude']) ? (float)$_POST['longitude'] : (isset($_GET['longitude']) ? (float)$_GET['longitude'] : null));

// Optional status field (string)
$status      = null;
if (is_array($asJson) && isset($asJson['status'])) {
    $status = trim((string)$asJson['status']);
} elseif (isset($_POST['status'])) {
    $status = trim((string)$_POST['status']);
} elseif (isset($_GET['status'])) {
    $status = trim((string)$_GET['status']);
}

// Basic validation guards
if ($sensor_id < 0) $sensor_id = 0;
if ($count < 0) $count = 0;
if ($brgy_id < 0) $brgy_id = 0;
if ($location_id < 0) $location_id = 0;

// ---- Optional: create aggregate table if not exists (idempotent) ----
$createAgg = "
CREATE TABLE IF NOT EXISTS sensor_agg_daily (
  date DATE NOT NULL,
  sensor_id INT NOT NULL,
  brgy_id INT NOT NULL,
  location_id INT NOT NULL,
  total_count INT NOT NULL DEFAULT 0,
  last_distance DOUBLE DEFAULT NULL,
  last_latitude DOUBLE DEFAULT NULL,
  last_longitude DOUBLE DEFAULT NULL,
  last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (date, sensor_id, brgy_id, location_id),
  KEY (brgy_id, date),
  KEY (location_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
@$conn->query($createAgg);

// ---- 1) Insert into `sensor` (optionally including `status` column if it exists) ----
// Detect if `status` column exists in sensor table
$hasStatusCol = false;
$colCheckSql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sensor' AND COLUMN_NAME = 'status' LIMIT 1";
if ($res = $conn->query($colCheckSql)) {
    $hasStatusCol = ($res->num_rows > 0);
    $res->close();
}

if ($hasStatusCol && $status !== null && $status !== '') {
    $sql = "INSERT INTO sensor (sensor_id, count, brgy_id, location_id, timestamp, distance, status)
            VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $dist = isset($distance) ? (float)$distance : 0.0;
        $stmt->bind_param("iiiids", $sensor_id, $count, $brgy_id, $location_id, $dist, $status);
        $rawOk = $stmt->execute();
        $rawMsg = $rawOk ? "✅ Raw insert OK (with status)" : ("❌ Raw insert failed: " . $stmt->error);
        $stmt->close();
    } else {
        $rawOk = false;
        $rawMsg = "❌ Prepare failed for raw insert (with status): " . $conn->error;
    }
} else {
    $sql = "INSERT INTO sensor (sensor_id, count, brgy_id, location_id, timestamp, distance)
            VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $dist = isset($distance) ? (float)$distance : 0.0; // bind requires a value
        $stmt->bind_param("iiiid", $sensor_id, $count, $brgy_id, $location_id, $dist);
        $rawOk = $stmt->execute();
        $rawMsg = $rawOk ? "✅ Raw insert OK" : ("❌ Raw insert failed: " . $stmt->error);
        $stmt->close();
    } else {
        $rawOk = false;
        $rawMsg = "❌ Prepare failed for raw insert: " . $conn->error;
    }
}

// ---- 2) Upsert into daily aggregate table ----
$aggSql = "
INSERT INTO sensor_agg_daily
  (date, sensor_id, brgy_id, location_id, total_count, last_distance, last_latitude, last_longitude)
VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
  total_count = total_count + VALUES(total_count),
  last_distance = VALUES(last_distance),
  last_latitude = VALUES(last_latitude),
  last_longitude = VALUES(last_longitude),
  last_updated = CURRENT_TIMESTAMP
";

$stmtAgg = $conn->prepare($aggSql);
if ($stmtAgg) {
    // Bind nullable floats: use nulls if not provided
    $ld = isset($distance) ? $distance : null;
    $la = isset($latitude) ? $latitude : null;
    $lo = isset($longitude) ? $longitude : null;

    // mysqli doesn't support direct NULL for double in bind_param; coerce to float when null not allowed
    // But columns allow NULL, so pass nulls via dynamic binding by using s and converting to null? Safer: cast to float or set to NULL by using set to null and use "d" still works when value is null in recent PHP; if not, default to 0.0.
    if ($ld === null) $ld = 0.0;
    if ($la === null) $la = 0.0;
    if ($lo === null) $lo = 0.0;

    $stmtAgg->bind_param("iiiiddd", $sensor_id, $brgy_id, $location_id, $count, $ld, $la, $lo);
    $aggOk = $stmtAgg->execute();
    $aggMsg = $aggOk ? "✅ Aggregation upsert OK" : ("❌ Agg upsert failed: " . $stmtAgg->error);
    $stmtAgg->close();
} else {
    $aggOk = false;
    $aggMsg = "❌ Prepare failed for agg upsert: " . $conn->error;
}

// ---- 3) Optional: also insert GPS lat/lng into `gps_location` when provided ----
$gpsOk = false;
$gpsMsg = "⚠️ GPS not provided";
if ($latitude !== null && $longitude !== null && is_numeric($latitude) && is_numeric($longitude)) {
    $gpsSql = "INSERT INTO gps_location (latitude, longitude, timestamp) VALUES (?, ?, NOW())";
    $stmtGps = $conn->prepare($gpsSql);
    if ($stmtGps) {
        $latf = (float)$latitude;
        $lngf = (float)$longitude;
        $stmtGps->bind_param("dd", $latf, $lngf);
        $gpsOk = $stmtGps->execute();
        $gpsMsg = $gpsOk ? "✅ GPS insert OK" : ("❌ GPS insert failed: " . $stmtGps->error);
        $stmtGps->close();
    } else {
        $gpsOk = false;
        $gpsMsg = "❌ Prepare failed for GPS insert: " . $conn->error;
    }
}

// Combined response (keep success if at least raw insert succeeded, so dashboard remains consistent)
$success = $rawOk;
echo json_encode([
    "status" => $success ? "success" : "error",
    "message" => $success ? "Inserted, aggregated, and gps handled" : "Insert/aggregate error",
    "raw" => $rawMsg,
    "aggregate" => $aggMsg,
    "gps" => $gpsMsg
]);

$conn->close();
?>
