<?php
require_once('connection.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$sql_pty = "SELECT * FROM product_type WHERE pty_show = 1 ORDER BY CONVERT(pty_name USING tis620) ASC";
$result_pty = mysqli_query($project_connect, $sql_pty) or die(mysqli_connect_error());

$target_dir = "uploads/products/";
$uploadOk = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prd_name = isset($_POST['prd_name']) ? mysqli_real_escape_string($project_connect, $_POST['prd_name']) : '';
    $prd_detail = isset($_POST['prd_detail']) ? mysqli_real_escape_string($project_connect, $_POST['prd_detail']) : '';
    $prd_price = isset($_POST['prd_price']) ? floatval($_POST['prd_price']) : 0.0;
    $pty_id = isset($_POST['pty_id']) ? intval($_POST['pty_id']) : 0;
    $prd_show = isset($_POST['prd_show']) && $_POST['prd_show'] == "1" ? 1 : 0;
    $main_img_index = isset($_POST['main_img']) && $_POST['main_img'] !== '' ? intval($_POST['main_img']) : -1;

    $sql_product = "INSERT INTO product (prd_name, prd_detail, prd_price, pty_id, prd_show) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($project_connect, $sql_product);
    mysqli_stmt_bind_param($stmt, "ssdii", $prd_name, $prd_detail, $prd_price, $pty_id, $prd_show);

    if (mysqli_stmt_execute($stmt)) {
        $prd_id = mysqli_insert_id($project_connect);

        if (!empty($_FILES['prd_img']['name'][0])) {
            foreach ($_FILES['prd_img']['tmp_name'] as $key => $tmp_name) {
                if (!empty($_FILES['prd_img']['name'][$key])) {
                    // ตรวจสอบรหัสข้อผิดพลาดของการอัปโหลด
                    $upload_error = $_FILES['prd_img']['error'][$key];
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
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลด: " . $_FILES['prd_img']['name'][$key] . " - " . $error_msg . " (Error Code: $upload_error)');</script>";
                        continue;
                    }

                    if (empty($tmp_name)) {
                        echo "<script>alert('ไม่พบข้อมูลไฟล์ชั่วคราว: " . $_FILES['prd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    $imageFileType = strtolower(pathinfo($_FILES['prd_img']['name'][$key], PATHINFO_EXTENSION));
                    $target_file = $target_dir . uniqid() . '.' . $imageFileType;

                    if (getimagesize($tmp_name) === false) {
                        echo "<script>alert('ไฟล์ที่เลือกไม่ใช่รูปภาพ: " . $_FILES['prd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if ($_FILES['prd_img']['size'][$key] > 10000000) {
                        echo "<script>alert('ขนาดไฟล์ใหญ่เกินไป (ไม่เกิน 10MB): " . $_FILES['prd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                        echo "<script>alert('อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG และ GIF เท่านั้น: " . $_FILES['prd_img']['name'][$key] . "');</script>";
                        continue;
                    }

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $prd_img_show = ($key == $main_img_index) ? 1 : 0;
                        $sql_img = "INSERT INTO product_img (prd_id, prd_img_name, prd_img_show) VALUES (?, ?, ?)";
                        $stmt_img = mysqli_prepare($project_connect, $sql_img);
                        mysqli_stmt_bind_param($stmt_img, "isi", $prd_id, $target_file, $prd_img_show);
                        mysqli_stmt_execute($stmt_img);
                        mysqli_stmt_close($stmt_img);
                    } else {
                        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์: " . $_FILES['prd_img']['name'][$key] . "');</script>";
                    }
                }
            }
        }

        header("Location: product_show.php");
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
    <title>เพิ่มรายการสินค้า</title>
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
        <h3 class="text-center mb-4">เพิ่มสินค้า</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="prd_name" class="form-label">ชื่อสินค้า</label>
                <input type="text" class="form-control" id="prd_name" name="prd_name" required>
            </div>
            <div class="mb-3">
                <label for="prd_detail" class="form-label">รายละเอียดสินค้า</label>
                <textarea class="form-control" id="prd_detail" name="prd_detail" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ประเภทสินค้า</label>
                <select id="pty_id" name="pty_id" class="form-control" required>
                    <option value="">--กรุณาเลือก--</option>
                    <?php while ($row_pty = mysqli_fetch_assoc($result_pty)) { ?>
                        <option value="<?php echo $row_pty['pty_id']; ?>"><?php echo htmlspecialchars($row_pty['pty_name'] ?? ''); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="prd_price" class="form-label">ราคาสินค้า</label>
                <input type="number" class="form-control" id="prd_price" name="prd_price" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="prd_img" class="form-label">รูปภาพสินค้า</label>
                <input type="file" class="form-control" id="prd_img" name="prd_img[]" multiple required>
            </div>
            <div class="mb-3">
                <label class="form-label">ตัวอย่างรูปภาพ (คลิกเพื่อเลือกรูปหลัก)</label>
                <div id="imagePreview" class="row"></div>
            </div>
            <input type="hidden" name="main_img" id="main_img" value="">
            <div class="mb-3">
                <label class="form-label">แสดงผล</label>
                <select name="prd_show" class="form-control">
                    <option value="1">แสดง</option>
                    <option value="0">ซ่อน</option>
                </select>
            </div>
            <div class="mb-5 text-center">
                <button type="submit" class="btn btn-primary">เพิ่มสินค้า</button>
                <a href="product_show.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

    <script>
        document.getElementById('prd_img').addEventListener('change', function() {
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
                        var deleteBtn = document.createElement('button');
                        deleteBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
                        deleteBtn.style.padding = '2px 5px';
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
                document.getElementById('prd_img').files = dataTransfer.files;
            }
        });
    </script>

</body>

</html>