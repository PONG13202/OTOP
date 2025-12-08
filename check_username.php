<?php
require_once('connection.php'); 

if (!empty($_POST["user_name"])) { // ตรวจสอบว่ามีการส่ง user_name มาหรือไม่
    $username = $_POST["user_name"];

    // ใช้ prepared statement เพื่อป้องกัน SQL Injection
    $stmt = $project_connect->prepare("SELECT * FROM user WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<span style='color: red;'>ชื่อผู้ใช้นี้ถูกใช้แล้ว</span>";
    } else {
        echo "<span style='color: green;'>สามารถใช้ชื่อผู้ใช้นี้ได้</span>";
    }

    $stmt->close();
} else {
    echo ""; // ถ้าไม่มีการส่ง user_name มา ไม่ต้องแสดงอะไรเลย
}
?>
