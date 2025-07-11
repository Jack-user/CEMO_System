<?php
require_once '../includes/conn.php';

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);

    $stmt = $pdo->prepare("SELECT email FROM client_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo json_encode(["exists" => $stmt->fetch() ? true : false]);
}
?>
