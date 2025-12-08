<?php
require_once('connection.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$target_dir = "uploads/product_type/";
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES["pty_img"]["name"] ?? '', PATHINFO_EXTENSION));
$target_file = $target_dir . uniqid() . '.' . $imageFileType; 

if (isset($_POST["pty_ok"]) && $_POST["pty_ok"] == "pty_ok") {
    if (!empty($_FILES["pty_img"]["name"])) {
        $check = getimagesize($_FILES["pty_img"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "<script>alert('ไฟล์ที่เลือกไม่ใช่รูปภาพ');</script>";
            $uploadOk = 0;
        }

        if ($_FILES["pty_img"]["size"] > 5000000) {
            echo "<script>alert('ขนาดไฟล์ใหญ่เกินไป (ไม่เกิน 5MB)');</script>";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "<script>alert('อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG และ GIF เท่านั้น');</script>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["pty_img"]["tmp_name"], $target_file)) {
                $pty_name = mysqli_real_escape_string($project_connect, $_POST['pty_name']);
                $pty_detail = mysqli_real_escape_string($project_connect, $_POST['pty_detail']);
                $pty_show = isset($_POST['pty_show']) && $_POST['pty_show'] == "1" ? 1 : 0;

                $sql_script = "INSERT INTO product_type (pty_name, pty_detail, pty_img, pty_show) 
                               VALUES ('$pty_name', '$pty_detail', '$target_file', '$pty_show')";

                if (mysqli_query($project_connect, $sql_script)) {
                    header("Location: product_show.php");
                    exit();
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
                }
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');</script>";
            }
        }
    } else {
        echo "<script>alert('กรุณาเลือกไฟล์รูปภาพ');</script>";
    }
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มประเภทสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
<div class="main-content">
    <div class="container mt-4">
        <h3 class="text-center mb-4">เพิ่มประเภทสินค้า</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pty_name" class="form-label">ชื่อประเภทสินค้า</label>
                <input type="text" class="form-control" id="pty_name" name="pty_name" required>
            </div>

            <div class="mb-3">
                <label for="pty_detail" class="form-label">รายละเอียด</label>
                <input type="text" class="form-control" id="pty_detail" name="pty_detail">
            </div>

            <div class="mb-3">
                <label for="pty_img" class="form-label">รูปภาพประเภทสินค้า</label>
                <input type="file" class="form-control" id="pty_img" name="pty_img" required>
            </div>

            <div class="mb-3">
                <select name="pty_show" class="form-control">
                    <option value="1">แสดง</option>
                    <option value="0">ซ่อน</option>
                </select>
            </div>

            <button type="submit" name="pty_ok" value="pty_ok" class="btn btn-primary">เพิ่มประเภทสินค้า</button>
            <a href="product_show.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
    </div>
</body>
</html>
