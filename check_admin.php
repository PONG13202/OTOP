<?php
require_once('connection.php');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $check_sql = "SELECT * FROM role_admin WHERE user_id = ?";
    $stmt = mysqli_prepare($project_connect, $check_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "exists"; // มีอยู่แล้ว
    } else {
        echo "available"; // ยังไม่มี
    }
}
?>
