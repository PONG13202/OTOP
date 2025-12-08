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

// ดึงข้อมูล contact
$stmt = $proj_connect->prepare("SELECT * FROM contact WHERE cont_id = ?");
$stmt->bind_param("i", $_GET["cont_id"]);
$stmt->execute();
$result = $stmt->get_result();
$row_result = $result->fetch_assoc();

if (isset($_POST["cmdedit"]) && $_POST["cmdedit"] == "ok") {
    if (empty($_POST['cont_name']) || empty($_POST['cont_icon']) || empty($_POST['cont_link'])) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน'); window.history.back();</script>";
        exit();
    }

    $stmt = $proj_connect->prepare("UPDATE contact SET cont_name = ?, cont_icon = ?, cont_link = ? WHERE cont_id = ?");
    $stmt->bind_param("sssi", $_POST['cont_name'], $_POST['cont_icon'], $_POST['cont_link'], $_POST['cont_id']);
    
    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขเสร็จสิ้น'); window.location='contact_show.php';</script>";
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
    <title>แก้ไขข้อมูลการติดต่อ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>แก้ไขข้อมูลการติดต่อ</h2>
        <form method="post">
            <div class="form-group">
                <label>ชื่อ:</label>
                <input type="text" class="form-control" name="cont_name" value="<?php echo htmlspecialchars($row_result['cont_name']); ?>">
            </div>
            <div class="form-group">
                <label>ไอคอน:</label>
                <input type="text" class="form-control" name="cont_icon" value="<?php echo htmlspecialchars($row_result['cont_icon']); ?>">
            </div>
            <div class="form-group">
                <label>ลิงก์:</label>
                <input type="text" class="form-control" name="cont_link" value="<?php echo htmlspecialchars($row_result['cont_link']); ?>">
            </div>
            <input type="hidden" name="cont_id" value="<?php echo $row_result['cont_id']; ?>">
            <input type="hidden" name="cmdedit" value="ok">
            <button type="submit" class="btn btn-success">บันทึก</button>
            <a href="contact_show.php" class="btn btn-secondary">กลับ</a>
        </form>
    </div>
</body>
</html>