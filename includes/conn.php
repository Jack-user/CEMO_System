<?php
// Database connection file for online
// $host = 'localhost'; 
// $dbname = 'u520834156_DBWasteTracker'; 
// $username = 'u520834156_userWT2025'; 
// $password = '^Lx|Aii1'; 

// Database connection file for local
$host = 'localhost'; 
$dbname = 'cemo_db'; 
$username = 'root'; 
$password = ''; 

$conn = new mysqli($host, $username, $password, $dbname);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
