<?php
require_once('connection.php');

// 2) ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
    header("Location: index.php");
    exit();
}

// 3) ตรวจสอบว่าผู้ใช้ล็อกอินแล้ว
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 4) ตรวจสอบพารามิเตอร์ loca_id
if (!isset($_GET['loca_id']) || empty($_GET['loca_id'])) {
    header("Location: cont_show.php");
    exit();
}

// 5) แปลง loca_id เป็น integer และตรวจสอบความถูกต้อง
$loca_id = filter_var($_GET['loca_id'], FILTER_VALIDATE_INT);
if ($loca_id === false || $loca_id <= 0) {
    header("Location: cont_show.php");
    exit();
}

// 6) ดึงข้อมูลสถานที่จากฐานข้อมูล
$sql_select = "SELECT * FROM locat WHERE loca_id = ?";
$stmt = mysqli_prepare($project_connect, $sql_select);
mysqli_stmt_bind_param($stmt, "i", $loca_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$location = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ถ้าไม่พบข้อมูล
if (!$location) {
    header("Location: cont_show.php");
    exit();
}

// 7) ประมวลผลฟอร์มเมื่อมีการส่งข้อมูล POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loca_name = trim($_POST['loca_name'] ?? '');
    $loca_link = trim($_POST['loca_link'] ?? '');
    $loca_map  = trim($_POST['loca_map'] ?? '');

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($loca_name)) {
        $error = "กรุณากรอกชื่อสถานที่";
    } else {
        // อัปเดตข้อมูล
        $sql_update = "UPDATE locat SET loca_name = ?, loca_link = ?, loca_map = ? WHERE loca_id = ?";
        $stmt_update = mysqli_prepare($project_connect, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssi", $loca_name, $loca_link, $loca_map, $loca_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            header("Location: cont_show.php?success=1");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดต: " . mysqli_error($project_connect);
        }
        mysqli_stmt_close($stmt_update);
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
    <title>แก้ไขสถานที่</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="main-content">
        <div class="container mt-4">
            <h2 class="text-center">แก้ไขข้อมูลสถานที่</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- ฟอร์มแก้ไขข้อมูลสถานที่ -->
            <form method="POST">
                <div class="mb-3">
                    <label for="loca_name" class="form-label">ชื่อสถานที่ <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        id="loca_name"
                        name="loca_name"
                        value="<?php echo htmlspecialchars($location['loca_name'], ENT_QUOTES); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="loca_link" class="form-label">ลิงก์สถานที่ (URL)</label>
                    <input
                        type="url"
                        class="form-control"
                        id="loca_link"
                        name="loca_link"
                        value="<?php echo htmlspecialchars($location['loca_link'], ENT_QUOTES); ?>">
                </div>

                <div class="mb-3">
                    <label for="loca_map" class="form-label">ฝังแผนที่ (Google Map Embed Code)</label>
                    <textarea
                        class="form-control"
                        id="loca_map"
                        name="loca_map"
                        rows="4"><?php echo htmlspecialchars($location['loca_map'] ?? '', ENT_QUOTES); ?></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    <a href="cont_show.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>