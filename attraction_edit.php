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

// ตรวจสอบว่าได้รับ att_id จาก URL หรือไม่
if (!isset($_GET['att_id']) || empty($_GET['att_id'])) {
    header("Location: attraction_show.php");
    exit();
}

$att_id = intval($_GET['att_id']);

// ดึงข้อมูลสถานที่ท่องเที่ยวปัจจุบัน
$sql_attraction = "SELECT * FROM attraction WHERE att_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_attraction);
mysqli_stmt_bind_param($stmt, "i", $att_id);
mysqli_stmt_execute($stmt);
$result_attraction = mysqli_stmt_get_result($stmt);
$attraction = mysqli_fetch_assoc($result_attraction);

if (!$attraction) {
    header("Location: attraction_show.php");
    exit();
}

// ดึงข้อมูลรูปภาพของสถานที่ท่องเที่ยว
$sql_images = "SELECT * FROM attraction_img WHERE att_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_images);
mysqli_stmt_bind_param($stmt, "i", $att_id);
mysqli_stmt_execute($stmt);
$result_images = mysqli_stmt_get_result($stmt);
$images = [];
while ($row = mysqli_fetch_assoc($result_images)) {
    $images[] = $row;
}

$target_dir = "uploads/attractions/";

// หาภาพหลักปัจจุบัน
$current_main_img_id = null;
foreach ($images as $img) {
    if ($img['att_img_show'] == 1) {
        $current_main_img_id = $img['att_img_id'];
        break;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $att_name = mysqli_real_escape_string($project_connect, $_POST['att_name']);
    $att_detail = mysqli_real_escape_string($project_connect, $_POST['att_detail']);
    $att_location = mysqli_real_escape_string($project_connect, $_POST['att_location']);
    $att_map = $_POST['att_map']; // ไม่ใช้ mysqli_real_escape_string เพราะใช้ prepared statement
    $att_show = ($_POST['att_show'] == "1") ? 1 : 0;
    $att_id = intval($_GET['att_id']);
    $main_img = isset($_POST['main_img']) ? $_POST['main_img'] : null;

    // ตรวจสอบโค้ดฝัง Google Maps
    if (!empty($att_map) && strpos($att_map, '<iframe') === false) {
        echo "<script>alert('กรุณากรอกโค้ดฝัง Google Maps ที่ถูกต้อง');</script>";
        exit();
    }

    // อัปเดตข้อมูลสถานที่ท่องเที่ยว
    $sql_update = "UPDATE attraction SET att_name=?, att_detail=?, att_location=?, att_map=?, att_show=? WHERE att_id=?";
    $stmt_update = mysqli_prepare($project_connect, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssssi", $att_name, $att_detail, $att_location, $att_map, $att_show, $att_id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    // ลบรูปภาพที่มีอยู่ (ถ้ามีการเลือก)
    if (isset($_POST['delete_img'])) {
        foreach ($_POST['delete_img'] as $img_id) {
            $sql_delete_img = "DELETE FROM attraction_img WHERE att_img_id = ? AND att_id = ?";
            $stmt_delete_img = mysqli_prepare($project_connect, $sql_delete_img);
            mysqli_stmt_bind_param($stmt_delete_img, "ii", $img_id, $att_id);
            mysqli_stmt_execute($stmt_delete_img);
            mysqli_stmt_close($stmt_delete_img);
        }
    }

    // อัปโหลดรูปภาพใหม่ (ถ้ามี)
    $new_img_mapping = [];
    if (!empty($_FILES['att_img']['name'][0])) {
        foreach ($_FILES['att_img']['tmp_name'] as $key => $tmp_name) {
            if (!empty($_FILES['att_img']['name'][$key])) {
                $imageFileType = strtolower(pathinfo($_FILES['att_img']['name'][$key], PATHINFO_EXTENSION));
                $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                if (getimagesize($tmp_name) !== false && in_array($imageFileType, ["jpg", "jpeg", "png", "gif"]) && $_FILES['att_img']['size'][$key] <= 10000000) {
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $att_img_show = 0;
                        $sql_insert_img = "INSERT INTO attraction_img (att_id, att_img_name, att_img_show) VALUES (?, ?, ?)";
                        $stmt_insert_img = mysqli_prepare($project_connect, $sql_insert_img);
                        mysqli_stmt_bind_param($stmt_insert_img, "isi", $att_id, $target_file, $att_img_show);
                        mysqli_stmt_execute($stmt_insert_img);
                        $new_img_id = mysqli_insert_id($project_connect);
                        $new_img_mapping['new_' . $key] = $new_img_id;
                        mysqli_stmt_close($stmt_insert_img);
                    }
                }
            }
        }
    }

    // ตั้งค่ารูปภาพหลัก
    if ($main_img !== null && $main_img !== '') {
        // รีเซ็ตภาพหลักทั้งหมดก่อน
        $sql_reset_main = "UPDATE attraction_img SET att_img_show = 0 WHERE att_id = ?";
        $stmt_reset_main = mysqli_prepare($project_connect, $sql_reset_main);
        mysqli_stmt_bind_param($stmt_reset_main, "i", $att_id);
        mysqli_stmt_execute($stmt_reset_main);
        mysqli_stmt_close($stmt_reset_main);

        if (is_numeric($main_img)) {
            // รูปภาพที่มีอยู่
            $sql_set_main = "UPDATE attraction_img SET att_img_show = 1 WHERE att_img_id = ? AND att_id = ?";
            $stmt_set_main = mysqli_prepare($project_connect, $sql_set_main);
            mysqli_stmt_bind_param($stmt_set_main, "ii", $main_img, $att_id);
            mysqli_stmt_execute($stmt_set_main);
            mysqli_stmt_close($stmt_set_main);
        } elseif (strpos($main_img, 'new_') === 0 && isset($new_img_mapping[$main_img])) {
            // รูปภาพใหม่
            $new_main_img_id = $new_img_mapping[$main_img];
            $sql_set_main = "UPDATE attraction_img SET att_img_show = 1 WHERE att_img_id = ? AND att_id = ?";
            $stmt_set_main = mysqli_prepare($project_connect, $sql_set_main);
            mysqli_stmt_bind_param($stmt_set_main, "ii", $new_main_img_id, $att_id);
            mysqli_stmt_execute($stmt_set_main);
            mysqli_stmt_close($stmt_set_main);
        }
    }

    header("Location: attraction_show.php");
    exit();
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขรายการสถานที่ท่องเที่ยว</title>
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
            <h3 class="text-center mb-4">แก้ไขสถานที่ท่องเที่ยว</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="att_id" value="<?php echo $att_id; ?>">
                <div class="mb-3">
                    <label for="att_name" class="form-label">ชื่อสถานที่</label>
                    <input type="text" class="form-control" id="att_name" name="att_name" value="<?php echo htmlspecialchars($attraction['att_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="att_detail" class="form-label">รายละเอียดสถานที่</label>
                    <textarea class="form-control" id="att_detail" name="att_detail" rows="3" required><?php echo htmlspecialchars($attraction['att_detail']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="att_location" class="form-label">สถานที่ตั้ง</label>
                    <input type="text" class="form-control" id="att_location" name="att_location" value="<?php echo htmlspecialchars($attraction['att_location']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="att_map" class="form-label">ฝัง Google Map (Embed Code)</label>
                    <textarea class="form-control" id="att_map" name="att_map" rows="4" oninput="previewMap()"><?php echo htmlspecialchars($attraction['att_map']); ?></textarea>
                    <div id="map-preview" class="mt-3" style="min-height: 300px;">
                        <?php if (!empty($attraction['att_map'])): ?>
                            <?php echo $attraction['att_map']; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="att_img" class="form-label">อัปโหลดรูปภาพใหม่</label>
                    <input type="file" class="form-control" id="att_img" name="att_img[]" multiple>
                </div>
                <div class="mb-3">
                    <label class="form-label">ตัวอย่างรูปภาพใหม่ (คลิกเพื่อเลือกรูปหลัก)</label>
                    <div id="imagePreview" class="row"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">รูปภาพที่มีอยู่ (คลิกเพื่อเลือกรูปหลัก หรือเลือกเพื่อลบ)</label>
                    <div id="existingImages" class="row">
                        <?php foreach ($images as $img): ?>
                            <div class="col-md-1 mb-2 position-relative" id="image-<?php echo $img['att_img_id']; ?>">
                                <img src="<?php echo $img['att_img_name']; ?>" class="img-thumbnail <?php echo $img['att_img_show'] ? 'selected' : ''; ?>" style="width: 100px; height: 100px; cursor: pointer;" data-index="<?php echo $img['att_img_id']; ?>">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" style="padding: 2px 5px;" onclick="deleteImage(<?php echo $img['att_img_id']; ?>)">X</button>
                                <input type="checkbox" name="delete_img[]" value="<?php echo $img['att_img_id']; ?>" style="display: none;" class="delete-checkbox">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" name="main_img" id="main_img" value="<?php echo $current_main_img_id; ?>">
                <div class="mb-3">
                    <label class="form-label">แสดงผล</label>
                    <select name="att_show" class="form-control">
                        <option value="1" <?php echo ($attraction['att_show'] == 1) ? 'selected' : ''; ?>>แสดง</option>
                        <option value="0" <?php echo ($attraction['att_show'] == 0) ? 'selected' : ''; ?>>ซ่อน</option>
                    </select>
                </div>
                <div class="mb-5 text-center">
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    <a href="attraction_show.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // จัดการรูปภาพที่มีอยู่
        document.querySelectorAll('#existingImages img').forEach(img => {
            img.onclick = function() {
                document.querySelectorAll('#existingImages img, #imagePreview img').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('main_img').value = this.dataset.index;
            };
        });

        // ลบรูปภาพที่มีอยู่
        function deleteImage(imgId) {
            var mainImg = document.getElementById('main_img').value;
            if (mainImg == imgId) {
                document.getElementById('main_img').value = ''; // ล้างค่า main_img
            }
            document.querySelector(`#image-${imgId} .delete-checkbox`).checked = true;
            document.getElementById(`image-${imgId}`).style.display = 'none';
        }

        // แสดงตัวอย่างและจัดการรูปภาพใหม่
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
                            if (document.getElementById('main_img').value == 'new_' + index) {
                                document.getElementById('main_img').value = '';
                            }
                        };

                        img.onclick = function() {
                            document.querySelectorAll('#existingImages img, #imagePreview img').forEach(el => el.classList.remove('selected'));
                            this.classList.add('selected');
                            document.getElementById('main_img').value = this.dataset.index;
                        };

                        col.appendChild(img);
                        col.appendChild(deleteBtn);
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
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
            var mainImg = document.getElementById('main_img').value;
            if (!mainImg) {
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