<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

if(!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $res = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_id DESC");
    $data = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit();
}

if(isset($_POST['action'])) {
    if($_POST['action'] == 'add_room') {
        $room_no = trim(mysqli_real_escape_string($conn, $_POST['room_no']));
        $room_type = trim(mysqli_real_escape_string($conn, $_POST['room_type']));
        $status = trim(mysqli_real_escape_string($conn, $_POST['status']));

        // Check overall total hostel rooms limit
        $chk1 = mysqli_query($conn, "SELECT COUNT(*) as tot FROM rooms");
        $r1 = mysqli_fetch_assoc($chk1);
        if($r1['tot'] >= 50) {
            echo json_encode(["status" => "error", "message" => "Rooms are already taken, there's no room left!"]);
            exit();
        }

        // Check specific room type max limit rule
        $chk2 = mysqli_query($conn, "SELECT COUNT(*) as tot_t FROM rooms WHERE room_type='$room_type'");
        $r2 = mysqli_fetch_assoc($chk2);
        if($r2['tot_t'] >= 25) {
            echo json_encode(["status" => "error", "message" => "Maximum limit of 25 rooms reached for this type."]);
            exit();
        }

        // Auto assign capacity value depending on room type
        $capacity = (strtolower($room_type) === 'ac') ? 5 : 4;

        // Server-side block to reject any capacity values violating standard rules
        if (strtolower($room_type) === 'ac' && $capacity > 5) {
            echo json_encode(["status" => "error", "message" => "Maximum capacity for AC rooms is 5!"]);
            exit();
        }
        if (strtolower($room_type) === 'non-ac' && $capacity > 4) {
            echo json_encode(["status" => "error", "message" => "Maximum capacity for Non-AC rooms is 4!"]);
            exit();
        }

        $q = "INSERT INTO rooms (room_no, room_type, capacity, current_occupancy, status) 
              VALUES ('$room_no', '$room_type', $capacity, 0, '$status')";
        
        if(mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }

    if($_POST['action'] == 'edit_room') {
        $room_id = intval($_POST['room_id']);
        $room_no = trim(mysqli_real_escape_string($conn, $_POST['room_no']));
        $room_type = trim(mysqli_real_escape_string($conn, $_POST['room_type']));
        $status = trim(mysqli_real_escape_string($conn, $_POST['status']));

        // Auto assign capacity value depending on room type
        $capacity = (strtolower($room_type) === 'ac') ? 5 : 4;

        // Server-side validation check before editing records
        if (strtolower($room_type) === 'ac' && $capacity > 5) {
            echo json_encode(["status" => "error", "message" => "Maximum capacity for AC rooms is 5!"]);
            exit();
        }
        if (strtolower($room_type) === 'non-ac' && $capacity > 4) {
            echo json_encode(["status" => "error", "message" => "Maximum capacity for Non-AC rooms is 4!"]);
            exit();
        }

        $q = "UPDATE rooms SET room_no='$room_no', room_type='$room_type', capacity=$capacity, status='$status' WHERE room_id=$room_id";
        
        if(mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }

    if($_POST['action'] == 'delete_room') {
        $room_id = intval($_POST['room_id']);
        if(mysqli_query($conn, "DELETE FROM rooms WHERE room_id=$room_id")) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }
}
exit();
?>