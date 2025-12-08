<?php
require_once('connection.php');

// ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cont_name = isset($_POST['cont_name']) ? mysqli_real_escape_string($project_connect, $_POST['cont_name']) : '';
    $cont_link = isset($_POST['cont_link']) ? mysqli_real_escape_string($project_connect, $_POST['cont_link']) : '';

    // บันทึกข้อมูลช่องทางการติดต่อ
    $sql_contact = "INSERT INTO contact (cont_name, cont_link) VALUES (?, ?)";
    $stmt = mysqli_prepare($project_connect, $sql_contact);
    mysqli_stmt_bind_param($stmt, "ss", $cont_name, $cont_link);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: cont_show.php"); // เปลี่ยนไปยังหน้าที่ต้องการหลังบันทึกสำเร็จ
        exit();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($project_connect) . "');</script>";
    }
    mysqli_stmt_close($stmt);
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มช่องทางการติดต่อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="main-content">
    <div class="container mt-4">
        <h3 class="text-center mb-4">เพิ่มช่องทางการติดต่อ</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="cont_name" class="form-label">ชื่อช่องทางการติดต่อ</label>
                <input type="text" class="form-control" id="cont_name" name="cont_name" required>
            </div>
            <div class="mb-3">
                <label for="cont_link" class="form-label">ลิงก์</label>
                <input type="url" class="form-control" id="cont_link" name="cont_link" required>
            </div>
            <div class="mb-5 text-center">
                <button type="submit" class="btn btn-primary">เพิ่มช่องทางการติดต่อ</button>
                <a href="cont_show.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
</body>

</html>