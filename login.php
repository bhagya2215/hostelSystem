<?php
session_start();
require("../conection/connect.php");
header("Content-Type: application/json");

// Tightened condition: Must exist AND must equal the string value 'true'
if(isset($_POST['btn_log']) && $_POST['btn_log'] === 'true'){
    
    if(empty($_POST['unametxt']) || empty($_POST['pwdtxt'])) {
        echo json_encode(["status" => "error", "message" => "All login fields are required."]);
        exit();
    }

    $uname = trim(mysqli_real_escape_string($conn, $_POST['unametxt']));
    $pwd   = trim(mysqli_real_escape_string($conn, $_POST['pwdtxt']));

    // Target the verified table name 'users'
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE username='$uname' AND role='admin'");

    if(mysqli_num_rows($sql) == 1){
        $row = mysqli_fetch_assoc($sql);
        if($pwd == $row['password']){
            $_SESSION['user'] = $row['username'];
            $_SESSION['type'] = 'admin';
            echo json_encode(["status" => "success", "message" => "Welcome " . $row['username'] . "!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect Password!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Username not found in the system!"]);
    }
} else {
    // This executes if btn_log is missing, unchecked, or set to 'false'
    echo json_encode(["status" => "error", "message" => "Invalid Trigger Execution."]);
}
exit(); 
