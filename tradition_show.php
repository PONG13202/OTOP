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

// การลบประเพณี
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['trd_id'])) {
    $trd_id = intval($_POST['trd_id']);

    // เริ่ม Transaction เพื่อให้การลบสมบูรณ์ทั้งหมด
    mysqli_begin_transaction($project_connect);

    try {
        // 1. ดึงข้อมูลรูปภาพจาก tradition_img เพื่อลบไฟล์
        $sql_images = "SELECT trd_img_name FROM tradition_img WHERE trd_id = ?";
        $stmt_images = mysqli_prepare($project_connect, $sql_images);
        mysqli_stmt_bind_param($stmt_images, "i", $trd_id);
        mysqli_stmt_execute($stmt_images);
        $result_images = mysqli_stmt_get_result($stmt_images);

        $image_files = [];
        while ($row = mysqli_fetch_assoc($result_images)) {
            $image_files[] = $row['trd_img_name'];
        }
        mysqli_stmt_close($stmt_images);

        // 2. ลบข้อมูลจาก tradition_img
        $sql_delete_img = "DELETE FROM tradition_img WHERE trd_id = ?";
        $stmt_img = mysqli_prepare($project_connect, $sql_delete_img);
        mysqli_stmt_bind_param($stmt_img, "i", $trd_id);
        $execute_img = mysqli_stmt_execute($stmt_img);
        mysqli_stmt_close($stmt_img);

        if (!$execute_img) {
            throw new Exception("Unable to delete data from tradition_img.");
        }

        // 3. ลบข้อมูลจาก tradition
        $sql_delete_tradition = "DELETE FROM tradition WHERE trd_id = ?";
        $stmt_tradition = mysqli_prepare($project_connect, $sql_delete_tradition);
        mysqli_stmt_bind_param($stmt_tradition, "i", $trd_id);
        $execute_tradition = mysqli_stmt_execute($stmt_tradition);
        mysqli_stmt_close($stmt_tradition);

        if (!$execute_tradition) {
            throw new Exception("Unable to delete data from tradition.");
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

// ดึงข้อมูลประเพณี
$sql_tradition = "SELECT tradition.*, trd_img_name 
                  FROM tradition 
                  LEFT JOIN tradition_img ON tradition.trd_id = tradition_img.trd_id AND tradition_img.trd_img_show = 1
                  ORDER BY CONVERT(tradition.trd_name USING tis620) ASC";
$result_tradition = mysqli_query($project_connect, $sql_tradition) or die(mysqli_error($project_connect));

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
    <style>
        /* สมมติหัวตาราง 6 ช่อง: ID, ชื่อประเภทสินค้า, รายละเอียด, รูป, สถานะ, จัดการ */

        /* ขยายเฉพาะคอลัมน์ที่ 3 (รายละเอียด) */
        table.dataTable th:nth-child(3),
        table.dataTable td:nth-child(3) {
            width: 320px;
            /* ปรับตามที่ต้องการ */
            white-space: normal;
            /* ให้แยกบรรทัดได้ตามปกติ */
        }

        table.dataTable th:nth-child(4),
        table.dataTable td:nth-child(4) {
            width: 120px;
            white-space: normal;
        }

        /* ป้องกันคอลัมน์ที่ 6 (จัดการ) ขึ้นบรรทัดใหม่ */
        table.dataTable th:nth-child(6),
        table.dataTable td:nth-child(6) {
            white-space: nowrap;
            width: 80px;
            /* ความกว้างตามต้องการ */
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="container mt-4">
            <h2 class="text-center mb-4">ประเพณี</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='tradition_add.php'">
                <i class="fas fa-plus"></i> เพิ่มประเพณี
            </button>

            <div class="table-responsive">
                <!-- Table Inside traditionTable -->
                <table id="traditionTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อประเพณี</th>
                            <th>รายละเอียดประเพณี</th>
                            <th>สถานะ</th>
                            <th>รูปภาพ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_tradition = mysqli_fetch_assoc($result_tradition)) { ?>
                            <tr>
                                <td><?php echo $row_tradition['trd_id']; ?></td>

                                <!-- Apply text-truncate styles here -->
                                <td class="text-truncate"
                                    style="max-width: 150px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row_tradition['trd_name']); ?>
                                </td>
                                <td class="text-truncate"
                                    style="max-width: 250px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row_tradition['trd_detail']); ?>
                                </td>

                                <td><?php echo ($row_tradition['trd_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($row_tradition['trd_img_name'] ?? ''); ?>"
                                        width="80"
                                        height="80"
                                        alt="รูปประเพณี">
                                </td>
                                <td>
                                    <a href="tradition_edit.php?trd_id=<?php echo $row_tradition['trd_id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="confirmDeleteTradition(
                            <?php echo $row_tradition['trd_id']; ?>, 
                            '<?php echo htmlspecialchars($row_tradition['trd_name']); ?>'
                        )">
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

            // ฟังก์ชันลบประเพณี
            window.confirmDeleteTradition = function(trdId, trdName) {
                Swal.fire({
                    title: `คุณต้องการลบประเพณี: ${trdId} ชื่อ: ${trdName} หรือไม่?`,
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
                                trd_id: trdId
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "ลบสำเร็จ!",
                                        text: "ประเพณีและรูปภาพถูกลบแล้ว",
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
            $('#traditionTable').DataTable({
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