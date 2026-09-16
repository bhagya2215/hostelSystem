<?php
session_start();
require("../conection/connect.php");

ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");

// FETCH: වගුවට දත්ත දීම
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $res = mysqli_query($conn, "SELECT id, username FROM users WHERE role='admin' ORDER BY id DESC");
    $data = [];
    if ($res) {
        while($row = mysqli_fetch_assoc($res)) { 
            $data[] = $row; 
        }
    }
    echo json_encode($data);
    exit();
}

// Actions
if(isset($_POST['action'])) {
    
    // --- ADD ADMIN ---
    if($_POST['action'] == 'add_admin') {
        // මෙතනදී JavaScript එකෙන් එන නියම නම් (IDs) පරීක්ෂා කරයි
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if(empty($username) || empty($password)) {
            echo json_encode(["status" => "error", "message" => "Fields are empty"]);
            exit();
        }

        $new_user = mysqli_real_escape_string($conn, $username);
        $new_pwd  = mysqli_real_escape_string($conn, $password);

        // එකම නම තියෙනවද බැලීම
        $check_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$new_user'");
        if(mysqli_num_rows($check_user) > 0){
            echo json_encode(["status" => "error", "message" => "Username taken"]);
            exit();
        }

        // ⚠️ මෙන්න මෙතනදී role එක කෙලින්ම 'admin' ලෙස වැටේ
        $q = "INSERT INTO users (username, password, role) VALUES ('$new_user', '$new_pwd', 'admin')";
        if(mysqli_query($conn, $q)){
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        exit();
    } 
    
    // --- DELETE ADMIN ---
    elseif($_POST['action'] == 'delete_admin') {
        $target_id = isset($_POST['id']) ? trim($_POST['id']) : '';
        if(empty($target_id)) {
            echo json_encode(["status" => "error"]);
            exit();
        }

        $target_id = mysqli_real_escape_string($conn, $target_id);
        $delete_q = "DELETE FROM users WHERE id='$target_id' AND role='admin'";
        if(mysqli_query($conn, $delete_q)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        exit();
    } 
}
?>