<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

if(!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $res = mysqli_query($conn, "SELECT * FROM payments ORDER BY payemnt_id DESC");
    $data = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit();
}

if(isset($_POST['action'])) {
    $action = $_POST['action'];

    if($action == 'add_payment') {
        $student_id = intval($_POST['student_id']);
        $amount = floatval($_POST['amount']);
        $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $month = !empty($_POST['month']) ? $_POST['month'] : date('F');
        $year = !empty($_POST['year']) ? intval($_POST['year']) : date('Y');

        $q = "INSERT INTO payments (student_id, amount, date, month, year, status) 
              VALUES ($student_id, $amount, '$date', '$month', $year, 'paid')";
              
        if(mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }

    if($action == 'mark_as_paid') {
        $payemnt_id = intval($_POST['payemnt_id']);
        if(mysqli_query($conn, "UPDATE payments SET status='paid' WHERE payemnt_id=$payemnt_id")) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }

    if($action == 'void_payment') {
        $payemnt_id = intval($_POST['payemnt_id']);
        if(mysqli_query($conn, "UPDATE payments SET status='void' WHERE payemnt_id=$payemnt_id")) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }
}
exit();
?>