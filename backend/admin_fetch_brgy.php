<?php
$query = "SELECT brgy_id, barangay, latitude, longitude, facebook_link, link_text FROM barangays_table";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>
