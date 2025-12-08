<?php
require_once('connection.php');

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่าได้รับค่า bnn_id จาก URL หรือไม่
if (isset($_GET['bnn_id']) && !empty($_GET['bnn_id'])) {
    $bnn_id = $_GET['bnn_id'];

    // ดึงข้อมูลแบนเนอร์จากฐานข้อมูล
    $sql_banner = "SELECT * FROM banner WHERE bnn_id = '$bnn_id'";
    $result_banner = mysqli_query($project_connect, $sql_banner);
    if (mysqli_num_rows($result_banner) > 0) {
        $row_banner = mysqli_fetch_assoc($result_banner);
    } else {
        echo "ไม่พบแบนเนอร์ที่คุณต้องการแก้ไข";
        exit();
    }
} else {
    echo "ไม่พบรหัสแบนเนอร์";
    exit();
}

if (isset($_POST['submit'])) {
    // รับค่าที่แก้ไขจากฟอร์ม
    $bnn_name = $_POST['bnn_name'];
    $bnn_desc = $_POST['bnn_desc'];
    $bnn_link = $_POST['bnn_link'];
    $bnn_show = $_POST['bnn_show'];

    // ตรวจสอบว่าได้อัพโหลดไฟล์รูปภาพใหม่หรือไม่
    // ตรวจสอบว่าได้อัพโหลดไฟล์รูปภาพใหม่หรือไม่
    $target_dir = "uploads/banners/";
    $target_file = $target_dir . basename($_FILES["bnn_img"]["name"] ?? $row_banner['bnn_img']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (isset($_FILES["bnn_img"]) && $_FILES["bnn_img"]["error"] == 0) {
        // ตรวจสอบว่าเป็นไฟล์ภาพหรือไม่
        $check = getimagesize($_FILES["bnn_img"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.<br>";
            $uploadOk = 0;
        }

        // ตรวจสอบขนาดไฟล์
        if ($_FILES["bnn_img"]["size"] > 5000000) {
            echo "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        // อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG, GIF
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }

        // ถ้าทุกอย่างโอเค ให้ทำการอัปโหลดไฟล์
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["bnn_img"]["tmp_name"], $target_file)) {
                // เมื่ออัปโหลดไฟล์สำเร็จ
                echo "The file " . htmlspecialchars(basename($_FILES["bnn_img"]["name"])) . " has been uploaded.<br>";
            } else {
                echo "Sorry, there was an error uploading your file.<br>";
            }
        } else {
            echo "Sorry, your file was not uploaded.<br>";
        }
    } else {
        // ใช้ไฟล์เดิมเมื่อไม่ได้อัพโหลดใหม่
        $target_file = $row_banner['bnn_img'];
    }

    // คำสั่ง SQL เพื่ออัปเดตข้อมูลแบนเนอร์
    $sql_update = "UPDATE banner SET bnn_name = '$bnn_name', bnn_desc = '$bnn_desc', bnn_link = '$bnn_link', bnn_img = '$target_file', bnn_show = '$bnn_show' WHERE bnn_id = '$bnn_id'";
    if (mysqli_query($project_connect, $sql_update)) {
        header("Location: banner.php"); // กลับไปที่หน้ารายการแบนเนอร์
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($project_connect);
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
    <title>แก้ไขแบนเนอร์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="main-content">
    <div class="container mt-4 form-container">
        <h3 class="text-center">แก้ไขแบนเนอร์</h3>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="bnn_name" class="form-label">ชื่อแบนเนอร์</label>
                <input type="text" class="form-control" id="bnn_name" name="bnn_name" value="<?php echo htmlspecialchars($row_banner['bnn_name']); ?>" required>
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
                    required><?php echo htmlspecialchars($row_banner['bnn_desc'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="bnn_link" class="form-label">ลิงก์</label>
                <input type="text" class="form-control" id="bnn_link" name="bnn_link" value="<?php echo htmlspecialchars($row_banner['bnn_link']); ?>" required>
            </div>

            <div class="current-image">
                <label for="bnn_img" class="form-label">รูปภาพปัจจุบัน</label>
                <br>
                <img src="<?php echo htmlspecialchars($row_banner['bnn_img']); ?>" alt="Current Banner Image" width="150">
            </div>

            <div class="mb-3">
                <label for="bnn_img" class="form-label">อัปโหลดรูปภาพใหม่</label>
                <input type="file" class="form-control" id="bnn_img" name="bnn_img">
            </div>

            <div class="mb-3">
                <label class="form-label">แสดงผล</label>
                <select name="bnn_show" class="form-control">
                    <option value="9">--กรุณาเลือก--</option>
                    <option value="1" <?php echo ($row_banner['bnn_show'] == 1) ? 'selected' : ''; ?>>แสดง</option>
                    <option value="0" <?php echo ($row_banner['bnn_show'] == 0) ? 'selected' : ''; ?>>ซ่อน</option>
                </select>
            </div>

            <div class="mb-4 text-center">
                <button type="submit" name="submit" class="btn btn-primary">อัปเดตแบนเนอร์</button>
                <a href="banner.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
    </div>
    <script>
        function validateForm() {
            var bnnName = document.getElementById("bnn_name").value.trim();
            var bnnDesc = document.getElementById("bnn_desc").value.trim();
            var bnnLink = document.getElementById("bnn_link").value.trim();
            var bnnShow = document.querySelector("select[name='bnn_show']").value;

            if (bnnName === "" || bnnDesc === "" || bnnLink === "" || bnnShow === "9") {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'ทุกช่องต้องกรอกข้อมูล และต้องเลือกแสดงผล',
                });
                return false; // หยุดการส่งฟอร์ม
            }
            return true; // ส่งฟอร์มได้
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>