<?php
require_once('connection.php');

// ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้ว
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบพารามิเตอร์ hist_id
if (!isset($_GET['hist_id']) || empty($_GET['hist_id'])) {
    header("Location: history_show.php");
    exit();
}

// แปลง hist_id เป็น integer และตรวจสอบความถูกต้อง
$hist_id = filter_var($_GET['hist_id'], FILTER_VALIDATE_INT);
if ($hist_id === false || $hist_id <= 0) {
    header("Location: history_show.php");
    exit();
}

// ดึงข้อมูลประวัติจากฐานข้อมูล
$sql_select = "SELECT * FROM history WHERE hist_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_select);
mysqli_stmt_bind_param($stmt, "i", $hist_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$history = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ถ้าไม่พบข้อมูล
if (!$history) {
    header("Location: history_show.php");
    exit();
}

// ประมวลผลฟอร์มเมื่อมีการส่งข้อมูล POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hist_title = trim($_POST['hist_title'] ?? '');
    $hist_desc = trim($_POST['hist_desc'] ?? '');
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($hist_title)) {
        $error = "กรุณากรอกหัวข้อประวัติ";
    } else {
        // จัดการอัปโหลดรูปภาพ (ถ้ามีการอัปโหลด)
        $hist_img = $history['hist_img']; // ค่าเริ่มต้นคือรูปภาพเดิม
        if (isset($_FILES['hist_img']) && $_FILES['hist_img']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/history/'; // โฟลเดอร์สำหรับเก็บรูปภาพ
            $file_name = uniqid() . '_' . basename($_FILES['hist_img']['name']);
            $upload_file = $upload_dir . $file_name;

            // ตรวจสอบประเภทไฟล์
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['hist_img']['tmp_name']);
            if (in_array($file_type, $allowed_types) && move_uploaded_file($_FILES['hist_img']['tmp_name'], $upload_file)) {
                $hist_img = $upload_file; // อัปเดตเส้นทางรูปภาพใหม่
                // ลบรูปภาพเก่า (ถ้ามี)
                if (!empty($history['hist_img']) && file_exists($history['hist_img'])) {
                    unlink($history['hist_img']);
                }
            } else {
                $error = "รูปภาพไม่ถูกต้องหรืออัปโหลดล้มเหลว";
            }
        }

        if (!isset($error)) {
            // อัปเดตข้อมูล
            $sql_update = "UPDATE history SET hist_title = ?, hist_desc = ?, hist_img = ? WHERE hist_id = ?";
            $stmt_update = mysqli_prepare($project_connect, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sssi", $hist_title, $hist_desc, $hist_img, $hist_id);
            
            if (mysqli_stmt_execute($stmt_update)) {
                header("Location: history_show.php?success=1");
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดต: " . mysqli_error($project_connect);
            }
            mysqli_stmt_close($stmt_update);
        }
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
    <title>แก้ไขประวัติ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="main-content">
        <div class="container mt-4">
            <h2 class="text-center">แก้ไขข้อมูลประวัติ</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- ฟอร์มแก้ไขข้อมูลประวัติ -->
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="hist_title" class="form-label">หัวข้อประวัติ <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        id="hist_title"
                        name="hist_title"
                        value="<?php echo htmlspecialchars($history['hist_title'], ENT_QUOTES); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="hist_desc" class="form-label">รายละเอียด</label>
                    <textarea
                        class="form-control"
                        id="hist_desc"
                        name="hist_desc"
                        rows="5"><?php echo htmlspecialchars($history['hist_desc'] ?? '', ENT_QUOTES); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="hist_img" class="form-label">รูปภาพ (ถ้ามีการอัปโหลดใหม่จะแทนที่รูปเดิม)</label>
                    <input
                        type="file"
                        class="form-control"
                        id="hist_img"
                        name="hist_img"
                        accept="image/*">
                    <?php if (!empty($history['hist_img'])): ?>
                        <div class="mt-2">
                            <p>รูปภาพปัจจุบัน:</p>
                            <img src="<?php echo htmlspecialchars($history['hist_img']); ?>" alt="รูปภาพประวัติ" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    <a href="history_show.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>