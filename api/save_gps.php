<?php
// ✅ Secure connection credentials
$host = "localhost";
$user = "u520834156_userWT2025";
$password = "^Lx|Aii1";
$database = "u520834156_DBWasteTracker";

// ✅ Connect to MySQL
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    die("❌ DB Connection failed: " . $conn->connect_error);
}

// ✅ Get and sanitize inputs
$lat = isset($_POST['latitude']) ? trim($_POST['latitude']) : null;
$lng = isset($_POST['longitude']) ? trim($_POST['longitude']) : null;

// ✅ Validate data
if (is_numeric($lat) && is_numeric($lng)) {
    $stmt = $conn->prepare("INSERT INTO gps_location (latitude, longitude) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("dd", $lat, $lng); // "dd" for float
        $stmt->execute();
        echo "✔ GPS saved";
        $stmt->close();
    } else {
        http_response_code(500);
        echo "❌ SQL Prepare error";
    }
} else {
    http_response_code(400);
    echo "❌ Invalid or missing latitude/longitude";
}

$conn->close();
?>
