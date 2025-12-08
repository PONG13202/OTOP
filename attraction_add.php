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

$target_dir = "uploads/attractions/";
$uploadOk = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $att_name = isset($_POST['att_name']) ? mysqli_real_escape_string($project_connect, $_POST['att_name']) : '';
    $att_detail = isset($_POST['att_detail']) ? mysqli_real_escape_string($project_connect, $_POST['att_detail']) : '';
    $att_location = isset($_POST['att_location']) ? mysqli_real_escape_string($project_connect, $_POST['att_location']) : '';
    $att_map = isset($_POST['att_map']) ? $_POST['att_map'] : ''; // ไม่ใช้ mysqli_real_escape_string เพราะใช้ prepared statement
    $att_show = isset($_POST['att_show']) && $_POST['att_show'] == "1" ? 1 : 0;
    $main_img_index = isset($_POST['main_img']) && $_POST['main_img'] !== '' ? $_POST['main_img'] : null;

    // ตรวจสอบโค้ดฝัง Google Maps
    if (!empty($att_map) && strpos($att_map, '<iframe') === false) {
        echo "<script>alert('กรุณากรอกโค้ดฝัง Google Maps ที่ถูกต้อง');</script>";
        exit();
    }

    // บันทึกข้อมูลสถานที่ท่องเที่ยว
    $sql_attraction = "INSERT INTO attraction (att_name, att_detail, att_location, att_map, att_show) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($project_connect, $sql_attraction);
    mysqli_stmt_bind_param($stmt, "ssssi", $att_name, $att_detail, $att_location, $att_map, $att_show);

    if (mysqli_stmt_execute($stmt)) {
        $att_id = mysqli_insert_id($project_connect);

        // อัปโหลดรูปภาพ
        if (!empty($_FILES['att_img']['name'][0])) {
            $new_img_mapping = []; // เก็บ mapping ระหว่าง index ชั่วคราวกับ att_img_id
            foreach ($_FILES['att_img']['tmp_name'] as $key => $tmp_name) {
                if (!empty($_FILES['att_img']['name'][$key])) {
                    $upload_error = $_FILES['att_img']['error'][$key];
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
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลด: " . $_FILES['att_img']['name'][$key] . " - " . $error_msg . "');</script>";
                        continue;
                    }

                    $imageFileType = strtolower(pathinfo($_FILES['att_img']['name'][$key], PATHINFO_EXTENSION));
                    $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                    if (getimagesize($tmp_name) === false) {
                        echo "<script>alert('ไฟล์ที่เลือกไม่ใช่รูปภาพ: " . $_FILES['att_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if ($_FILES['att_img']['size'][$key] > 10000000) {
                        echo "<script>alert('ขนาดไฟล์ใหญ่เกินไป (ไม่เกิน 10MB): " . $_FILES['att_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                        echo "<script>alert('อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG และ GIF เท่านั้น: " . $_FILES['att_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $att_img_show = 0; // เริ่มต้นไม่ตั้งเป็นหลัก
                        $sql_img = "INSERT INTO attraction_img (att_id, att_img_name, att_img_show) VALUES (?, ?, ?)";
                        $stmt_img = mysqli_prepare($project_connect, $sql_img);
                        mysqli_stmt_bind_param($stmt_img, "isi", $att_id, $target_file, $att_img_show);
                        mysqli_stmt_execute($stmt_img);
                        $new_img_id = mysqli_insert_id($project_connect);
                        $new_img_mapping['new_' . $key] = $new_img_id; // เก็บ mapping
                        mysqli_stmt_close($stmt_img);
                    }
                }
            }

            // ตั้งค่ารูปภาพหลัก
            if ($main_img_index !== null && isset($new_img_mapping[$main_img_index])) {
                $new_main_img_id = $new_img_mapping[$main_img_index];
                $sql_set_main = "UPDATE attraction_img SET att_img_show = 1 WHERE att_img_id = ? AND att_id = ?";
                $stmt_set_main = mysqli_prepare($project_connect, $sql_set_main);
                mysqli_stmt_bind_param($stmt_set_main, "ii", $new_main_img_id, $att_id);
                mysqli_stmt_execute($stmt_set_main);
                mysqli_stmt_close($stmt_set_main);
            }
        }

        header("Location: attraction_show.php");
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
    <title>เพิ่มสถานที่ท่องเที่ยว</title>
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
            <h3 class="text-center mb-4">เพิ่มสถานที่ท่องเที่ยว</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="att_name" class="form-label">ชื่อสถานที่</label>
                    <input type="text" class="form-control" id="att_name" name="att_name" required>
                </div>
                <div class="mb-3">
                    <label for="att_detail" class="form-label">รายละเอียดสถานที่</label>
                    <textarea class="form-control" id="att_detail" name="att_detail" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="att_location" class="form-label">สถานที่ตั้ง</label>
                    <input type="text" class="form-control" id="att_location" name="att_location" required>
                </div>
                <div class="mb-3">
                    <label for="att_map" class="form-label">ฝัง Google Map (Embed Code)</label>
                    <textarea class="form-control" id="att_map" name="att_map" rows="4" oninput="previewMap()"></textarea>
                    <div id="map-preview" class="mt-3" style="min-height: 300px;">
                        <p class="text-muted">กรุณาวางโค้ดฝังเพื่อดูตัวอย่างแผนที่</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="att_img" class="form-label">รูปภาพสถานที่</label>
                    <input type="file" class="form-control" id="att_img" name="att_img[]" multiple required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ตัวอย่างรูปภาพ (คลิกเพื่อเลือกรูปหลัก)</label>
                    <div id="imagePreview" class="row"></div>
                </div>
                <input type="hidden" name="main_img" id="main_img" value="">
                <div class="mb-3">
                    <label class="form-label">แสดงผล</label>
                    <select name="att_show" class="form-control">
                        <option value="1">แสดง</option>
                        <option value="0">ซ่อน</option>
                    </select>
                </div>
                <div class="mb-5 text-center">
                    <button type="submit" class="btn btn-primary">เพิ่มสถานที่ท่องเที่ยว</button>
                    <a href="attraction_show.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // แสดงตัวอย่างและจัดการรูปภาพ
        document.getElementById('att_img').addEventListener('change', function() {
            var previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            var fileList = Array.from(this.files);

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
                        img.dataset.index = 'new_' + index;

                        var deleteBtn = document.createElement('button');
                        deleteBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
                        deleteBtn.style.padding = '2px 5px';
                        deleteBtn.innerText = 'X';
                        deleteBtn.onclick = function() {
                            fileList.splice(index, 1);
                            updateFileInput(fileList);
                            previewContainer.removeChild(col);
                            if (document.getElementById('main_img').value === 'new_' + index) {
                                document.getElementById('main_img').value = '';
                            }
                        };

                        img.onclick = function() {
                            Array.from(previewContainer.querySelectorAll('img')).forEach(el => el.classList.remove('selected'));
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

            function updateFileInput(newFileList) {
                var dataTransfer = new DataTransfer();
                newFileList.forEach(file => dataTransfer.items.add(file));
                document.getElementById('att_img').files = dataTransfer.files;
            }
        });

        // ตรวจสอบการเลือกรูปภาพหลักก่อนส่งฟอร์ม
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!document.getElementById('main_img').value) {
                e.preventDefault();
                alert('กรุณาเลือกรูปภาพหลักสำหรับสถานที่ท่องเที่ยวก่อนบันทึก');
            }
        });

        // แสดงตัวอย่าง Google Maps
        function previewMap() {
            const embedCode = document.getElementById('att_map').value.trim();
            const previewDiv = document.getElementById('map-preview');
            
            if (embedCode === '') {
                previewDiv.innerHTML = '<p class="text-muted">กรุณาวางโค้ดฝังเพื่อดูตัวอย่างแผนที่</p>';
            } else if (embedCode.includes('<iframe')) {
                const cleanedCode = embedCode.replace(/\\"/g, '"'); // แก้ไขการ escape quotes
                previewDiv.innerHTML = cleanedCode;
            } else {
                previewDiv.innerHTML = '<p class="text-danger">โค้ดฝัง Google Maps ไม่ถูกต้อง กรุณาตรวจสอบ</p>';
            }
        }

        // เรียกฟังก์ชันตอนโหลดหน้าเพื่อแสดงตัวอย่างเริ่มต้น
        document.addEventListener('DOMContentLoaded', function() {
            previewMap();
        });
    </script>
</body>
</html>