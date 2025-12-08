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

$target_dir = "uploads/banners/";
$target_file = $target_dir . basename($_FILES["bnn_img"]["name"] ?? '');
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if (isset($_POST["bnn_ok"]) && $_POST["bnn_ok"] == "bnn_ok") {

    if (isset($_FILES["bnn_img"]) && $_FILES["bnn_img"]["error"] == 0) {
        // ตรวจสอบว่าเป็นไฟล์ภาพหรือไม่
        $check = getimagesize($_FILES["bnn_img"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "<script>alert('File is not an image.');</script>";
            $uploadOk = 0;
        }

        // ตรวจสอบขนาดไฟล์
        if ($_FILES["bnn_img"]["size"] > 5000000) {
            echo "<script>alert('Sorry, your file is too large.');</script>";
            $uploadOk = 0;
        }

        // อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG, GIF
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            $uploadOk = 0;
        }

        // ถ้าทุกอย่างโอเค ให้ทำการอัปโหลดไฟล์
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["bnn_img"]["tmp_name"], $target_file)) {
                // เมื่ออัปโหลดไฟล์สำเร็จ
                echo "<script>alert('The file " . htmlspecialchars(basename($_FILES["bnn_img"]["name"])) . " has been uploaded.');</script>";

                // หลังจากอัปโหลดไฟล์แล้ว insert ข้อมูลลงในฐานข้อมูล
                $bnn_name = $_POST['bnn_name'];
                $bnn_desc = $_POST['bnn_desc'];
                $bnn_link = $_POST['bnn_link'];

                // คำสั่ง SQL เพื่อเพิ่มข้อมูลแบนเนอร์
                $sql_script = "INSERT INTO banner (bnn_name, bnn_desc, bnn_link, bnn_img) 
                               VALUES ('$bnn_name', '$bnn_desc', '$bnn_link', '$target_file')";
                $result = mysqli_query($project_connect, $sql_script) or die(mysqli_connect_error());
                mysqli_close($project_connect);
                
                header("Location: banner.php");
            } else {
                echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            }
        } else {
            echo "<script>alert('Sorry, your file was not uploaded.');</script>";
        }
    } else {
        echo "<script>alert('No file selected or there was an error with the file upload.');</script>";
    }
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่ม Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</head>

<body>
    
<div class="main-content">
    <div class="container mt-4">
        <h3 class="text-center mb-4">เพิ่ม Banner</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="bnn_name" class="form-label">ชื่อแบนเนอร์</label>
                <input type="text" class="form-control" id="bnn_name" name="bnn_name" placeholder="ชื่อแบนเนอร์" required>
            </div>
            <div class="mb-3">
                <label for="bnn_desc" class="form-label">คำอธิบาย</label>
                <textarea
                    class="form-control"
                    id="bnn_desc"
                    name="bnn_desc"
                    rows="4"
                    placeholder="กรุณากรอกคำอธิบาย"
                    style="min-height: 120px; resize: vertical;"
                    required></textarea>
            </div>
            <div class="mb-3">
                <label for="bnn_link" class="form-label">ลิงก์</label>
                <input type="text" class="form-control" id="bnn_link" name="bnn_link" placeholder="ลิงก์" required>
            </div>

            <div class="mb-3">
                <label for="bnn_img" class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" id="bnn_img" name="bnn_img" >
            </div>
            <div class="form-group text-center mt-4">
            <button type="submit" name="bnn_ok" value="bnn_ok" class="btn btn-primary">เพิ่มแบนเนอร์</button>
            <a href="banner.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
</body>

</html>
