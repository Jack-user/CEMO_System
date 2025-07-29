<?php
// No session start needed for this simple check
include '../includes/conn.php';

if (isset($_GET['contact'])) {
    $contact = trim($_GET['contact']);

    // Validate contact format
    if (!preg_match('/^[0-9]{11}$/', $contact)) {
         echo "invalid_format"; // Signal invalid format
         exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT admin_id FROM admin_table WHERE contact = ?");
    mysqli_stmt_bind_param($stmt, "s", $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "exists";
    } else {
        echo "available";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "missing_parameter";
}
mysqli_close($conn);
?>