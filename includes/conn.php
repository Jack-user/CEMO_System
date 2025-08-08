<?php
// Database connection file
// $host = 'localhost'; 
// $dbname = 'u520834156_DBWasteTracker'; 
// $username = 'u520834156_userWT2025'; 
// $password = '^Lx|Aii1'; 

$host = 'localhost'; 
$dbname = 'cemo_db'; 
$username = 'root'; 
$password = ''; 


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("MySQLi Connection failed: " . $conn->connect_error);
}

?>
