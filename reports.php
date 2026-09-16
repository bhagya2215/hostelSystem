<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

// Security checkpoint: Only allow reports if admin is logged in
if(!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit();
}

/**
 * FETCH ROUTINE: Automatically loads all generated report logs 
 * into the HTML preview panel upon request or page load.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // We select all logged system reports to render in your dashboard grid layout
    $res = mysqli_query($conn, "SELECT * FROM reports ORDER BY id DESC");
    $data = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit();
}

// Get the action from the request body
$action = $_POST['action'] ?? '';

// --- ACTION 1: GENERATE & LOG NEW REPORT ---
if($action == 'generate_report') {
    $payment_id   = !empty($_POST['payment_id']) ? intval($_POST['payment_id']) : 'NULL';
    $report_type  = mysqli_real_escape_string($conn, $_POST['report_type'] ?? 'general');
    $generated_by = mysqli_real_escape_string($conn, $_POST['generated_by'] ?? 'Admin_System');
    $date         = date('Y-m-d');

    // Matches your specific 'genarated_date' and 'genarated_by' structural database taxonomy
    $q = "INSERT INTO reports (payment_id, report_type, genarated_date, genarated_by) 
          VALUES ($payment_id, '$report_type', '$date', '$generated_by')";
          
    if(mysqli_query($conn, $q)) {
        echo json_encode(["status" => "success", "message" => "Report log entry created successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
} 

// --- ACTION 2: PURGE/DELETE AN EXISTING REPORT LOG entry ---
elseif($action == 'delete_report') {
    // Handles numeric ID verification checkpoints safely
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        $q = "DELETE FROM reports WHERE id = $id";
        
        if (mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success", "message" => "Report deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid or missing ID."]);
    }
} 

// --- Handle Unknown or Corrupted Action streams ---
else {
    echo json_encode(["status" => "error", "message" => "Invalid or missing action instruction sequence."]);
}
exit();
?>