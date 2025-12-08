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

// ตรวจสอบว่าได้รับ trd_id จาก URL หรือไม่
if (!isset($_GET['trd_id']) || empty($_GET['trd_id'])) {
    header("Location: tradition_show.php");
    exit();
}

$trd_id = intval($_GET['trd_id']);

// ดึงข้อมูลประเพณีปัจจุบัน
$sql_tradition = "SELECT * FROM tradition WHERE trd_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_tradition);
mysqli_stmt_bind_param($stmt, "i", $trd_id);
mysqli_stmt_execute($stmt);
$result_tradition = mysqli_stmt_get_result($stmt);
$tradition = mysqli_fetch_assoc($result_tradition);

if (!$tradition) {
    header("Location: tradition_show.php");
    exit();
}

// ดึงข้อมูลรูปภาพของประเพณี
$sql_images = "SELECT * FROM tradition_img WHERE trd_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_images);
mysqli_stmt_bind_param($stmt, "i", $trd_id);
mysqli_stmt_execute($stmt);
$result_images = mysqli_stmt_get_result($stmt);
$images = [];
while ($row = mysqli_fetch_assoc($result_images)) {
    $images[] = $row;
}

// ดึงข้อมูลประเภทประเพท (ถ้ามี)
$sql_pty = "SELECT * FROM product_type ORDER BY CONVERT(pty_name USING tis620) ASC";
$result_pty = mysqli_query($project_connect, $sql_pty) or die(mysqli_error($project_connect));

$target_dir = "uploads/traditions/";

// หาภาพหลักปัจจุบัน
$current_main_img_id = null;
foreach ($images as $img) {
    if ($img['trd_img_show'] == 1) {
        $current_main_img_id = $img['trd_img_id'];
        break;
    }
}

// ประมวลผลเมื่อฟอร์มถูกส่ง
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trd_name = mysqli_real_escape_string($project_connect, $_POST['trd_name']);
    $trd_detail = mysqli_real_escape_string($project_connect, $_POST['trd_detail']);
    $trd_show = ($_POST['trd_show'] == "1") ? 1 : 0;
    $main_img = isset($_POST['main_img']) && !empty($_POST['main_img']) ? $_POST['main_img'] : null;

    // อัปเดตข้อมูลประเพณี
    $sql_update = "UPDATE tradition SET trd_name=?, trd_detail=?, trd_show=? WHERE trd_id=?";
    $stmt_update = mysqli_prepare($project_connect, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssii", $trd_name, $trd_detail, $trd_show, $trd_id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    // ลบรูปภาพที่มีอยู่ (ถ้ามีการเลือก)
    if (isset($_POST['delete_img'])) {
        foreach ($_POST['delete_img'] as $img_id) {
            $sql_delete_img = "DELETE FROM tradition_img WHERE trd_img_id = ? AND trd_id = ?";
            $stmt_delete_img = mysqli_prepare($project_connect, $sql_delete_img);
            mysqli_stmt_bind_param($stmt_delete_img, "ii", $img_id, $trd_id);
            mysqli_stmt_execute($stmt_delete_img);
            mysqli_stmt_close($stmt_delete_img);
        }
    }

    // อัปโหลดรูปภาพใหม่ (ถ้ามี)
    $new_img_mapping = []; // เก็บ mapping ระหว่าง index ชั่วคราวกับ trd_img_id
    if (!empty($_FILES['trd_img']['name'][0])) {
        foreach ($_FILES['trd_img']['tmp_name'] as $key => $tmp_name) {
            if (!empty($_FILES['trd_img']['name'][$key])) {
                $imageFileType = strtolower(pathinfo($_FILES['trd_img']['name'][$key], PATHINFO_EXTENSION));
                $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                if (getimagesize($tmp_name) !== false && in_array($imageFileType, ["jpg", "jpeg", "png", "gif"]) && $_FILES['trd_img']['size'][$key] <= 10000000) {
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $trd_img_show = 0; // เริ่มต้นไม่ตั้งเป็นหลัก
                        $sql_insert_img = "INSERT INTO tradition_img (trd_id, trd_img_name, trd_img_show) VALUES (?, ?, ?)";
                        $stmt_insert_img = mysqli_prepare($project_connect, $sql_insert_img);
                        mysqli_stmt_bind_param($stmt_insert_img, "isi", $trd_id, $target_file, $trd_img_show);
                        mysqli_stmt_execute($stmt_insert_img);
                        $new_img_id = mysqli_insert_id($project_connect); // รับ ID ของรูปภาพใหม่
                        $new_img_mapping['new_' . $key] = $new_img_id; // เก็บ mapping
                        mysqli_stmt_close($stmt_insert_img);
                    }
                }
            }
        }
    }

    // ตั้งค่ารูปภาพหลัก
    if ($main_img !== null) {
        // รีเซ็ตภาพหลักทั้งหมดก่อน
        $sql_reset_main = "UPDATE tradition_img SET trd_img_show = 0 WHERE trd_id = ?";
        $stmt_reset_main = mysqli_prepare($project_connect, $sql_reset_main);
        mysqli_stmt_bind_param($stmt_reset_main, "i", $trd_id);
        mysqli_stmt_execute($stmt_reset_main);
        mysqli_stmt_close($stmt_reset_main);

        if (is_numeric($main_img)) {
            // กรณีเป็นรูปภาพที่มีอยู่ (ใช้ trd_img_id)
            $sql_set_main = "UPDATE tradition_img SET trd_img_show = 1 WHERE trd_img_id = ? AND trd_id = ?";
            $stmt_set_main = mysqli_prepare($project_connect, $sql_set_main);
            mysqli_stmt_bind_param($stmt_set_main, "ii", $main_img, $trd_id);
            mysqli_stmt_execute($stmt_set_main);
            mysqli_stmt_close($stmt_set_main);
        } elseif (strpos($main_img, 'new_') === 0 && isset($new_img_mapping[$main_img])) {
            // กรณีเป็นรูปภาพใหม่ (ใช้ mapping เพื่อหา trd_img_id)
            $new_main_img_id = $new_img_mapping[$main_img];
            $sql_set_main = "UPDATE tradition_img SET trd_img_show = 1 WHERE trd_img_id = ? AND trd_id = ?";
            $stmt_set_main = mysqli_prepare($project_connect, $sql_set_main);
            mysqli_stmt_bind_param($stmt_set_main, "ii", $new_main_img_id, $trd_id);
            mysqli_stmt_execute($stmt_set_main);
            mysqli_stmt_close($stmt_set_main);
        }
    }

    header("Location: tradition_show.php");
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
    <title>แก้ไขรายการประเพณี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- เพิ่ม SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <h3 class="text-center mb-4">แก้ไขประเพณี</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="trd_id" value="<?php echo $trd_id; ?>">
            <div class="mb-3">
                <label for="trd_name" class="form-label">ชื่อประเพณี</label>
                <input type="text" class="form-control" id="trd_name" name="trd_name" value="<?php echo htmlspecialchars($tradition['trd_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="trd_detail" class="form-label">รายละเอียดประเพณี</label>
                <textarea class="form-control" id="trd_detail" name="trd_detail" rows="3" required><?php echo htmlspecialchars($tradition['trd_detail']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="trd_img" class="form-label">อัปโหลดรูปภาพใหม่</label>
                <input type="file" class="form-control" id="trd_img" name="trd_img[]" multiple>
            </div>
            <div class="mb-3">
                <label class="form-label">ตัวอย่างรูปภาพใหม่ (คลิกเพื่อเลือกรูปหลัก)</label>
                <div id="imagePreview" class="row"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปภาพที่มีอยู่ (คลิกเพื่อเลือกรูปหลัก หรือเลือกเพื่อลบ)</label>
                <div id="existingImages" class="row">
                    <?php foreach ($images as $img): ?>
                        <div class="col-md-1 mb-2 position-relative" id="image-<?php echo $img['trd_img_id']; ?>">
                            <img src="<?php echo $img['trd_img_name']; ?>" class="img-thumbnail <?php echo $img['trd_img_show'] ? 'selected' : ''; ?>" style="width: 100px; height: 100px; cursor: pointer;" data-index="<?php echo $img['trd_img_id']; ?>">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" style="padding: 2px 5px;" onclick="deleteImage(<?php echo $img['trd_img_id']; ?>)">X</button>
                            <input type="checkbox" name="delete_img[]" value="<?php echo $img['trd_img_id']; ?>" style="display: none;" class="delete-checkbox">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="main_img" id="main_img" value="<?php echo $current_main_img_id; ?>">
            <div class="mb-3">
                <label class="form-label">แสดงผล</label>
                <select name="trd_show" class="form-control">
                    <option value="1" <?php echo ($tradition['trd_show'] == 1) ? 'selected' : ''; ?>>แสดง</option>
                    <option value="0" <?php echo ($tradition['trd_show'] == 0) ? 'selected' : ''; ?>>ซ่อน</option>
                </select>
            </div>
            <div class="mb-5 text-center">
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                <a href="tradition_show.php" class="btn btn-secondary">ยกเลิก</a>
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
        document.getElementById('trd_img').addEventListener('change', function() {
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
                        img.dataset.index = 'new_' + index; // ใช้ "new_" เพื่อแยกจากรูปภาพที่มีอยู่

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
                document.getElementById('trd_img').files = dataTransfer.files;
            }
        });

        // ตรวจสอบการเลือกรูปภาพหลักก่อนส่งฟอร์ม
        document.querySelector('form').addEventListener('submit', function(e) {
            var mainImg = document.getElementById('main_img').value;
            if (!mainImg) {
                e.preventDefault(); // หยุดการส่งฟอร์ม
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกรูปภาพหลัก',
                    text: 'โปรดเลือกรูปภาพหลักสำหรับประเพณีก่อนบันทึก'
                });
            }
        });
    </script>
</body>

</html>