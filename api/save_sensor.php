<?php
$servername = "localhost";
$username = "root"; // default for XAMPP
$password = "";     // default for XAMPP
$dbname = "cemo_db"; // change to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from URL
$count = isset($_GET['count']) ? intval($_GET['count']) : 0;
$location_id = isset($_GET['location_id']) ? intval($_GET['location_id']) : 1; // default location

// Insert into your table
$sql = "INSERT INTO sensor (`count`, `location_id`) VALUES ($count, $location_id)";

if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
