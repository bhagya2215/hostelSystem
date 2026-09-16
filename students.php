<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

if(!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $res = mysqli_query($conn, "SELECT * FROM students ORDER BY student_id DESC");
    $data = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $data[] = $row; 
    }
    echo json_encode($data);
    exit();
}

if(isset($_POST['action'])) {
    if($_POST['action'] == 'add_student') {
        if(empty($_POST['name']) || empty($_POST['nic']) || empty($_POST['phone_no'])) {
            echo json_encode(["status" => "error", "message" => "Required fields missing."]);
            exit();
        }
        
        $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
        $nic = trim(mysqli_real_escape_string($conn, $_POST['nic']));
        $gender = trim(mysqli_real_escape_string($conn, $_POST['gender']));
        $dob = trim(mysqli_real_escape_string($conn, $_POST['dob']));
        $address = trim(mysqli_real_escape_string($conn, $_POST['address']));
        $phone_no = trim(mysqli_real_escape_string($conn, $_POST['phone_no']));
        $parent_name = trim(mysqli_real_escape_string($conn, $_POST['parent_name']));
        $parent_no = trim(mysqli_real_escape_string($conn, $_POST['parent_no']));
        $reg_date = !empty($_POST['register_date']) ? trim(mysqli_real_escape_string($conn, $_POST['register_date'])) : date('Y-m-d');

        $chk = mysqli_query($conn, "SELECT student_id FROM students WHERE nic='$nic'");
        if(mysqli_num_rows($chk) > 0){
            echo json_encode(["status" => "error", "message" => "NIC already exists."]); 
            exit();
        }

        $q = "INSERT INTO students (name, nic, gender, dob, address, phone_no, parent_name, parent_no, register_date) 
              VALUES ('$name', '$nic', '$gender', '$dob', '$address', '$phone_no', '$parent_name', '$parent_no', '$reg_date')";
        
        if(mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    } 

    if($_POST['action'] == 'edit_student') {
        $student_id = intval($_POST['student_id']);
        $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
        $nic = trim(mysqli_real_escape_string($conn, $_POST['nic']));
        $gender = trim(mysqli_real_escape_string($conn, $_POST['gender']));
        $dob = trim(mysqli_real_escape_string($conn, $_POST['dob']));
        $address = trim(mysqli_real_escape_string($conn, $_POST['address']));
        $phone_no = trim(mysqli_real_escape_string($conn, $_POST['phone_no']));
        $parent_name = trim(mysqli_real_escape_string($conn, $_POST['parent_name']));
        $parent_no = trim(mysqli_real_escape_string($conn, $_POST['parent_no']));

        $q = "UPDATE students SET name='$name', nic='$nic', gender='$gender', dob='$dob', address='$address', 
              phone_no='$phone_no', parent_name='$parent_name', parent_no='$parent_no' WHERE student_id=$student_id";
        
        if(mysqli_query($conn, $q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }

    if($_POST['action'] == 'delete_student') {
        $student_id = intval($_POST['student_id']);
        if(mysqli_query($conn, "DELETE FROM students WHERE student_id=$student_id")) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }
}
exit();
?>