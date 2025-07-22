<?php
include '../includes/conn.php';

$field = $_GET['field'];
$value = $_GET['value'];
$client_id = $_GET['client_id'];

$validFields = ['email', 'contact'];
if (!in_array($field, $validFields)) exit;

$query = "SELECT * FROM client_table WHERE $field = '$value' AND client_id != '$client_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo ucfirst($field) . " already exists.";
}
?>
