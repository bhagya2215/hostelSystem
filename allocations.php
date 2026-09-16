<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

// Access control check for system security
if(!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit();
}

// Fetch all allocations list when direct table grid refresh triggers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $res = mysqli_query($conn, "SELECT * FROM allocations ORDER BY allocation_id DESC");
    $data = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit();
}

// Handle administrative CRUD operational actions
if(isset($_POST['action'])) {
    
    // Process 1: Allocate a student into a specific hostel room
    if($_POST['action'] == 'allocate_room') {
        $student_id = intval($_POST['student_id']);
        $room_id = intval($_POST['room_id']);
        $status = !empty($_POST['status']) ? trim(mysqli_real_escape_string($conn, $_POST['status'])) : 'active';
        $alloc_date = !empty($_POST['allocation_date']) ? trim(mysqli_real_escape_string($conn, $_POST['allocation_date'])) : date('Y-m-d');

        // Fetch targets room metrics to check capacity configurations
        $res_rm = mysqli_query($conn, "SELECT room_type, capacity, current_occupancy, status FROM rooms WHERE room_id=$room_id");
        $room = mysqli_fetch_assoc($res_rm);

        if($room['status'] === 'maintenance') {
            echo json_encode(["status" => "error", "message" => "Room is under maintenance!"]);
            exit();
        }

        // --- HUMANIZED ENHANCEMENT: Strict strict capacity threshold check ---
        $room_type = $room['room_type'];
        $current_occupancy = intval($room['current_occupancy']);

        // Block allocation if AC room already has reached 5 students limit
        if ($room_type === 'AC' && $current_occupancy >= 5) {
            echo json_encode(["status" => "error", "message" => "Cannot allocate! AC rooms cannot exceed a maximum capacity of 5 students."]);
            exit();
        }

        // Block allocation if Non-AC room already has reached 4 students limit
        if ($room_type === 'Non-AC' && $current_occupancy >= 4) {
            echo json_encode(["status" => "error", "message" => "Cannot allocate! Non-AC rooms cannot exceed a maximum capacity of 4 students."]);
            exit();
        }
        // ---------------------------------------------------------------------

        // Standard safety check if database counts somehow mismatch
        if($room['current_occupancy'] >= $room['capacity'] || $room['status'] === 'full') {
            echo json_encode(["status" => "error", "message" => "Room is already full!"]);
            exit();
        }

        // Insert new structural boundary rules inside the allocations dataset
        $insert_alloc = "INSERT INTO allocations (student_id, room_id, allocation_date, status, leave_date) 
                         VALUES ($student_id, $room_id, '$alloc_date', '$status', NULL)";
        
        if(mysqli_query($conn, $insert_alloc)) {
            $new_count = $room['current_occupancy'] + 1;
            
            // Re-evaluate if room status changes to full based on type limits
            $room_status = 'available';
            if (($room_type === 'AC' && $new_count >= 5) || ($room_type === 'Non-AC' && $new_count >= 4) || ($new_count >= $room['capacity'])) {
                $room_status = 'full';
            }
            
            mysqli_query($conn, "UPDATE rooms SET current_occupancy=$new_count, status='$room_status' WHERE room_id=$room_id");
            echo json_encode(["status" => "success"]);
        }
    }

    // Process 2: Release student from hostel room (Check-out system)
    if($_POST['action'] == 'release_room') {
        $allocation_id = intval($_POST['allocation_id']);
        $leave_date = date('Y-m-d');

        // Trace original property inventory data boundary
        $res_al = mysqli_query($conn, "SELECT room_id FROM allocations WHERE allocation_id=$allocation_id");
        $alloc_data = mysqli_fetch_assoc($res_al);
        $room_id = $alloc_data['room_id'];

        if(mysqli_query($conn, "UPDATE allocations SET status='checked_out', leave_date='$leave_date' WHERE allocation_id=$allocation_id")) {
            $res_rm = mysqli_query($conn, "SELECT current_occupancy FROM rooms WHERE room_id=$room_id");
            $room = mysqli_fetch_assoc($res_rm);
            $new_count = max(0, $room['current_occupancy'] - 1);
            
            // Revert status safely back to available on student departure
            mysqli_query($conn, "UPDATE rooms SET current_occupancy=$new_count, status='available' WHERE room_id=$room_id");
            echo json_encode(["status" => "success"]);
        }
    }
}
exit();
?>