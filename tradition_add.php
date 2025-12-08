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

$target_dir = "uploads/traditions/";
$uploadOk = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trd_name = isset($_POST['trd_name']) ? mysqli_real_escape_string($project_connect, $_POST['trd_name']) : '';
    $trd_detail = isset($_POST['trd_detail']) ? mysqli_real_escape_string($project_connect, $_POST['trd_detail']) : '';
    $trd_show = isset($_POST['trd_show']) && $_POST['trd_show'] == "1" ? 1 : 0;
    $main_img_index = isset($_POST['main_img']) && $_POST['main_img'] !== '' ? intval($_POST['main_img']) : -1;

    // บันทึกข้อมูลประเพณี
    $sql_tradition = "INSERT INTO tradition (trd_name, trd_detail, trd_show) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($project_connect, $sql_tradition);
    mysqli_stmt_bind_param($stmt, "ssi", $trd_name, $trd_detail, $trd_show);

    if (mysqli_stmt_execute($stmt)) {
        $trd_id = mysqli_insert_id($project_connect);

        // อัปโหลดรูปภาพ
        if (!empty($_FILES['trd_img']['name'][0])) {
            foreach ($_FILES['trd_img']['tmp_name'] as $key => $tmp_name) {
                if (!empty($_FILES['trd_img']['name'][$key])) {
                    // ตรวจสอบรหัสข้อผิดพลาดของการอัปโหลด
                    $upload_error = $_FILES['trd_img']['error'][$key];
                    if ($upload_error !== UPLOAD_ERR_OK) {
                        $error_messages = [
                            UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกิน upload_max_filesize',
                            UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินที่ฟอร์มกำหนด',
                            UPLOAD_ERR_PARTIAL => 'ไฟล์ถูกอัปโหลดบางส่วน',
                            UPLOAD_ERR_NO_FILE => 'ไม่มีไฟล์ถูกอัปโหลด',
                            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
                            UPLOAD_ERR_CANT_WRITE => 'เขียนไฟล์ลงดิสก์ไม่ได้',
                            UPLOAD_ERR_EXTENSION => 'ส่วนขยาย PHP หยุดการอัปโหลด'
                        ];
                        $error_msg = $error_messages[$upload_error] ?? 'ข้อผิดพลาดไม่ทราบสาเหตุ';
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลด: " . $_FILES['trd_img']['name'][$key] . " - " . $error_msg . " (Error Code: $upload_error)');</script>";
                        continue;
                    }

                    if (empty($tmp_name)) {
                        echo "<script>alert('ไม่พบข้อมูลไฟล์ชั่วคราว: " . $_FILES['trd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    $imageFileType = strtolower(pathinfo($_FILES['trd_img']['name'][$key], PATHINFO_EXTENSION));
                    $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                    if (getimagesize($tmp_name) === false) {
                        echo "<script>alert('ไฟล์ที่เลือกไม่ใช่รูปภาพ: " . $_FILES['trd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if ($_FILES['trd_img']['size'][$key] > 10000000) {
                        echo "<script>alert('ขนาดไฟล์ใหญ่เกินไป (ไม่เกิน 10MB): " . $_FILES['trd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                        echo "<script>alert('อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG และ GIF เท่านั้น: " . $_FILES['trd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $trd_img_show = ($key == $main_img_index) ? 1 : 0;
                        $sql_img = "INSERT INTO tradition_img (trd_id, trd_img_name, trd_img_show) VALUES (?, ?, ?)";
                        $stmt_img = mysqli_prepare($project_connect, $sql_img);
                        mysqli_stmt_bind_param($stmt_img, "isi", $trd_id, $target_file, $trd_img_show);
                        mysqli_stmt_execute($stmt_img);
                        mysqli_stmt_close($stmt_img);
                    } else {
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์: " . $_FILES['trd_img']['name'][$key] . "');</script>";
                    }
                }
            }
        }

        header("Location: tradition_show.php");
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
    <title>เพิ่มประเพณี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .img-thumbnail.selected {
            border: 3px solid #007bff;
            background-color: #e9f5ff;
        }

    </style>
</head>

<body>
    
<div class="main-content">
    <div class="container mt-4">
        <h3 class="text-center mb-4">เพิ่มประเพณี</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="trd_name" class="form-label">ชื่อประเพณี</label>
                <input type="text" class="form-control" id="trd_name" name="trd_name" required>
            </div>
            <div class="mb-3">
                <label for="trd_detail" class="form-label">รายละเอียดประเพณี</label>
                <textarea class="form-control" id="trd_detail" name="trd_detail" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="trd_img" class="form-label">รูปภาพประเพณี</label>
                <input type="file" class="form-control" id="trd_img" name="trd_img[]" multiple required>
            </div>
            <div class="mb-3">
                <label class="form-label">ตัวอย่างรูปภาพ (คลิกเพื่อเลือกรูปหลัก)</label>
                <div id="imagePreview" class="row"></div>
            </div>
            <input type="hidden" name="main_img" id="main_img" value="">
            <div class="mb-3">
                <label class="form-label">แสดงผล</label>
                <select name="trd_show" class="form-control">
                    <option value="1">แสดง</option>
                    <option value="0">ซ่อน</option>
                </select>
            </div>
            <div class="mb-5 text-center">
                <button type="submit" class="btn btn-primary">เพิ่มประเพณี</button>
                <a href="tradition_show.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
    </div>

    <script>
        document.getElementById('trd_img').addEventListener('change', function() {
            var previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            var fileList = Array.from(this.files); // สร้างรายการไฟล์ใหม่

            fileList.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var col = document.createElement('div');
                        col.className = 'col-md-1 mb-2 position-relative';

                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail';
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.cursor = 'pointer';
                        img.dataset.index = index;

                        // ปุ่มลบ
                        // ปุ่มลบ
                        var deleteBtn = document.createElement('button');
                        deleteBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0'; // ใช้คลาส Bootstrap
                        deleteBtn.style.padding = '2px 5px'; // ปรับขนาดตามต้องการ
                        deleteBtn.innerText = 'X';
                        deleteBtn.onclick = function() {
                            fileList.splice(index, 1); // ลบไฟล์ออกจากรายการ
                            updateFileInput(fileList);
                            previewContainer.removeChild(col);
                        };
                        img.onclick = function() {
                            Array.from(previewContainer.querySelectorAll('img')).forEach(function(el) {
                                el.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            document.getElementById('main_img').value = this.dataset.index;
                        };

                        col.appendChild(img);
                        col.appendChild(deleteBtn);
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('ไฟล์ที่เลือกไม่ใช่รูปภาพ: ' + file.name);
                }
            });

            // ฟังก์ชันอัปเดตค่า input file
            function updateFileInput(newFileList) {
                var dataTransfer = new DataTransfer();
                newFileList.forEach(file => dataTransfer.items.add(file));
                document.getElementById('trd_img').files = dataTransfer.files;
            }
        });
    </script>

</body>

</html>