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

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$project_connect) {
    die("Database connection failed");
}

// ดึงข้อมูลประวัติจากตาราง history
$locations = mysqli_query($project_connect, "SELECT * FROM history ORDER BY hist_title ASC");
?>

<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลประวัติ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-content">
        <div class="main-content container mt-4">
            <section>
                <h2 class="mb-4">จัดการประวัติ</h2>
                <table class="table table-striped" id="locationTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ประวัติความเป็นมา</th>
                            <th>รายละเอียด</th>
                            <th>รูปภาพ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($locations)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['hist_id']) ?></td>
                                <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['hist_title']) ?>">
                                    <?= htmlspecialchars($row['hist_title']) ?>
                                </td>
                                <td class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($row['hist_desc']) ?>">
                                    <?= htmlspecialchars($row['hist_desc']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['hist_img'])): ?>
                                        <img src="<?= htmlspecialchars($row['hist_img']) ?>" alt="ภาพประวัติ" style="max-width: 100px;">
                                    <?php else: ?>
                                        ไม่มีรูปภาพ
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="history_edit.php?hist_id=<?= $row['hist_id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#locationTable').DataTable({
                "language": {
                    "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่มีข้อมูลที่แสดง",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "search": "ค้นหา:",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                }
            });
        });
    </script>
</body>
</html>