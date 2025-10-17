<?php
// Test database connection for online deployment
header('Content-Type: application/json');

// Test with online credentials
$host = "localhost";
$username = "u520834156_userWT2025";
$password = "^Lx|Aii1";
$dbname = "u520834156_DBWasteTracker";

echo "Testing database connection...\n";
echo "Host: $host\n";
echo "Username: $username\n";
echo "Database: $dbname\n\n";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo json_encode([
            "status" => "error",
            "message" => "Connection failed: " . $conn->connect_error
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "Database connection successful!",
            "server_info" => $conn->server_info
        ]);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Exception: " . $e->getMessage()
    ]);
}
?>
