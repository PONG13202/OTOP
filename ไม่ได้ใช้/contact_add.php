<?php
require_once('connection.php');

// ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'role_admin') {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (isset($_POST["cmdadd"]) && $_POST["cmdadd"] == "ok") {
    if (empty($_POST['cont_name']) || empty($_POST['cont_icon']) || empty($_POST['cont_link'])) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน'); window.history.back();</script>";
        exit();
    }

    $stmt = $proj_connect->prepare("INSERT INTO contact (cont_name, cont_icon, cont_link) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['cont_name'], $_POST['cont_icon'], $_POST['cont_link']);
    
    if ($stmt->execute()) {
        echo "<script>alert('เพิ่มช่องทางการติดต่อเสร็จสิ้น'); window.location='contact_show.php';</script>";
        exit();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด'); window.history.back();</script>";
        exit();
    }
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html>
<head>
    <title>เพิ่มข้อมูลการติดต่อ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>เพิ่มข้อมูลการติดต่อ</h2>
        <form method="post">
            <div class="form-group">
                <label>ชื่อ:</label>
                <input type="text" class="form-control" name="cont_name">
            </div>
            <div class="form-group">
                <label>ไอคอน:</label>
                <input type="text" class="form-control" name="cont_icon">
            </div>
            <div class="form-group">
                <label>ลิงก์:</label>
                <input type="text" class="form-control" name="cont_link">
            </div>
            <input type="hidden" name="cmdadd" value="ok">
            <button type="submit" class="btn btn-success">บันทึก</button>
            <a href="contact_show.php" class="btn btn-secondary">กลับ</a>
        </form>
    </div>
</body>
</html>