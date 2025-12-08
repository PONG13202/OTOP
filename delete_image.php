<?php
require_once('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['img_id'])) {
    $img_id = intval($_POST['img_id']);

    // ลบรูปภาพจากฐานข้อมูล
    $sql_delete = "DELETE FROM product_img WHERE prd_img_id = ?";
    $stmt = mysqli_prepare($project_connect, $sql_delete);
    mysqli_stmt_bind_param($stmt, "i", $img_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "ลบรูปภาพเรียบร้อยแล้ว";
    } else {
        echo "ไม่พบรูปภาพที่ต้องการลบ";
    }
}
?>