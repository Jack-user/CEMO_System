<?php
// ✅ Secure connection credentials
$host = 'localhost'; 
$database = 'cemo_db'; 
$user = 'root'; 
$password = ''; 

// ✅ Connect to MySQL
// Connect to MySQL and log errors
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    file_put_contents("gps_debug.txt", date("Y-m-d H:i:s") . " DB Connection Error: " . $conn->connect_error . "\n", FILE_APPEND);
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
        if ($stmt->execute()) {
            echo "✔ GPS saved";
            file_put_contents("gps_debug.txt", date("Y-m-d H:i:s") . " SQL Insert Success: $lat, $lng\n", FILE_APPEND);
        } else {
            http_response_code(500);
            echo "❌ SQL Execute error: " . $stmt->error;
            file_put_contents("gps_debug.txt", date("Y-m-d H:i:s") . " SQL Execute Error: " . $stmt->error . "\n", FILE_APPEND);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "❌ SQL Prepare error: " . $conn->error;
        file_put_contents("gps_debug.txt", date("Y-m-d H:i:s") . " SQL Prepare Error: " . $conn->error . "\n", FILE_APPEND);
    }
} else {
    http_response_code(400);
    echo "❌ Invalid or missing latitude/longitude";
    file_put_contents("gps_debug.txt", date("Y-m-d H:i:s") . " Invalid Data: lat=$lat, lng=$lng\n", FILE_APPEND);
}

$conn->close();
?>