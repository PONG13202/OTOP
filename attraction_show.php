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

// การลบสถานที่ท่องเที่ยว
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['att_id'])) {
    $att_id = intval($_POST['att_id']);

    // เริ่ม Transaction เพื่อให้การลบสมบูรณ์ทั้งหมด
    mysqli_begin_transaction($project_connect);

    try {
        // 1. ดึงข้อมูลรูปภาพจาก attraction_img เพื่อลบไฟล์
        $sql_images = "SELECT att_img_name FROM attraction_img WHERE att_id = ?";
        $stmt_images = mysqli_prepare($project_connect, $sql_images);
        mysqli_stmt_bind_param($stmt_images, "i", $att_id);
        mysqli_stmt_execute($stmt_images);
        $result_images = mysqli_stmt_get_result($stmt_images);

        $image_files = [];
        while ($row = mysqli_fetch_assoc($result_images)) {
            $image_files[] = $row['att_img_name'];
        }
        mysqli_stmt_close($stmt_images);

        // 2. ลบข้อมูลจาก attraction_img
        $sql_delete_img = "DELETE FROM attraction_img WHERE att_id = ?";
        $stmt_img = mysqli_prepare($project_connect, $sql_delete_img);
        mysqli_stmt_bind_param($stmt_img, "i", $att_id);
        $execute_img = mysqli_stmt_execute($stmt_img);
        mysqli_stmt_close($stmt_img);

        if (!$execute_img) {
            throw new Exception("Unable to delete data from attraction_img.");
        }

        // 3. ลบข้อมูลจาก attraction
        $sql_delete_attraction = "DELETE FROM attraction WHERE att_id = ?";
        $stmt_attraction = mysqli_prepare($project_connect, $sql_delete_attraction);
        mysqli_stmt_bind_param($stmt_attraction, "i", $att_id);
        $execute_attraction = mysqli_stmt_execute($stmt_attraction);
        mysqli_stmt_close($stmt_attraction);

        if (!$execute_attraction) {
            throw new Exception("Unable to delete data from attraction.");
        }

        // 4. ลบไฟล์รูปภาพจากโฟลเดอร์
        foreach ($image_files as $file) {
            if (file_exists($file)) {
                if (!unlink($file)) {
                    throw new Exception("Unable to delete image file: $file");
                }
            }
        }

        // Commit transaction ถ้าทุกอย่างสำเร็จ
        mysqli_commit($project_connect);
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        // Rollback ถ้ามีข้อผิดพลาด
        mysqli_rollback($project_connect);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// ดึงข้อมูลสถานที่ท่องเที่ยว
$sql_attraction = "SELECT attraction.*, att_img_name 
                   FROM attraction 
                   LEFT JOIN attraction_img ON attraction.att_id = attraction_img.att_id AND attraction_img.att_img_show = 1
                   ORDER BY CONVERT(attraction.att_name USING tis620) ASC";
$result_attraction = mysqli_query($project_connect, $sql_attraction) or die(mysqli_error($project_connect));

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($project_connect);
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ayutthaya</title>
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
        <div class="container mt-4">
            <h2 class="text-center mb-4">สถานที่ท่องเที่ยว</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='attraction_add.php'">
                <i class="fas fa-plus"></i> เพิ่มสถานที่ท่องเที่ยว
            </button>

            <div class="table-responsive">
                <table id="attractionTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อสถานที่</th>
                            <th>รายละเอียดสถานที่</th>
                            <th>สถานที่ตั้ง</th>
                            <th>สถานะ</th>
                            <th>รูปภาพ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_attraction = mysqli_fetch_assoc($result_attraction)) { ?>
                            <tr>
                                <td><?php echo $row_attraction['att_id']; ?></td>
                                <td><?php echo htmlspecialchars($row_attraction['att_name']); ?></td>
                                <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row_attraction['att_detail']); ?>">
                                    <?php echo htmlspecialchars($row_attraction['att_detail']); ?>
                                </td>
                                <td class="text-truncate" style="max-width: 190px;" title="<?php echo htmlspecialchars($row_attraction['att_location']); ?>">
                                    <?php echo htmlspecialchars($row_attraction['att_location']); ?>
                                </td>
                                <td><?php echo ($row_attraction['att_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row_attraction['att_img_name'] ?? ''); ?>" width="80" height="80" alt="รูปสถานที่ท่องเที่ยว"></td>
                                <td>
                                    <a href="attraction_edit.php?att_id=<?php echo $row_attraction['att_id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDeleteAttraction(<?php echo $row_attraction['att_id']; ?>, '<?php echo htmlspecialchars($row_attraction['att_name']); ?>')">
                                        <i class="fas fa-trash"></i> ลบ
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // ฟังก์ชันลบสถานที่ท่องเที่ยว
            window.confirmDeleteAttraction = function(attId, attName) {
                Swal.fire({
                    title: `คุณต้องการลบสถานที่ท่องเที่ยว: ${attId} ชื่อ: ${attName} หรือไม่?`,
                    text: "หากลบแล้วจะไม่สามารถกู้คืนได้ รวมถึงรูปภาพที่เกี่ยวข้อง!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "ใช่, ลบเลย!",
                    cancelButtonText: "ยกเลิก"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            data: {
                                att_id: attId
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "ลบสำเร็จ!",
                                        text: "สถานที่ท่องเที่ยวและรูปภาพถูกลบแล้ว",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        title: "เกิดข้อผิดพลาด!",
                                        text: res.message || "ไม่สามารถลบข้อมูลได้",
                                        icon: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: "เกิดข้อผิดพลาด!",
                                    text: "ไม่สามารถลบข้อมูลได้",
                                    icon: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            }

            // Initialize DataTables
            $('#attractionTable').DataTable({
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