<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "hosteldb"; // Matches your database name perfectly!

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Database connection failure."]);
    exit();
}
?>
