<?php
require_once('connection.php');
include('sidebar.php'); 
include('navbar.php');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!$project_connect) {
    die(json_encode(['status' => 'error', 'message' => 'เชื่อมต่อฐานข้อมูลไม่ได้']));
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bnn_id'])) {
    $bnn_id = $_POST['bnn_id'];

    // ตรวจสอบการมีอยู่ของข้อมูล
    $sql_check = "SELECT bnn_id FROM banner WHERE bnn_id = ?";
    $stmt_check = mysqli_prepare($project_connect, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $bnn_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        // ลบข้อมูล
        $sql_delete = "DELETE FROM banner WHERE bnn_id = ?";
        $stmt_delete = mysqli_prepare($project_connect, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $bnn_id);

        if (mysqli_stmt_execute($stmt_delete)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ลบข้อมูลไม่สำเร็จ: ' . mysqli_error($project_connect)]);
        }
        
        mysqli_stmt_close($stmt_delete);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลที่ต้องการลบ']);
    }

    mysqli_stmt_close($stmt_check);
    mysqli_close($project_connect);
    exit();
}
// ดึงข้อมูลแบนเนอร์
$sql_banner = "SELECT * FROM banner ORDER BY CONVERT(bnn_name USING tis620) ASC";
$result_banner = mysqli_query($project_connect, $sql_banner) or die(mysqli_error($project_connect));

mysqli_close($project_connect);
?>

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
            <h2 class="text-center mb-4">รายการแบนเนอร์</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='banner_add.php'">
                <i class="fas fa-plus"></i> เพิ่มแบนเนอร์
            </button>

            <div class="table-responsive">
                <table id="BannerTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อแบนเนอร์</th>
                            <th>คำอธิบาย</th>
                            <th>ลิงก์</th>
                            <th>รูปภาพ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_banner = mysqli_fetch_assoc($result_banner)) { ?>
                            <tr>
                                <td><?php echo $row_banner['bnn_id']; ?></td>

                                <!-- ชื่อแบนเนอร์ -->
                                <td><?php echo htmlspecialchars($row_banner['bnn_name']); ?></td>

                                <!-- คำอธิบาย (ตัดข้อความพร้อมแสดง title) -->
                                <td class="text-truncate" style="max-width: 200px;"
                                    title="<?php echo htmlspecialchars($row_banner['bnn_desc']); ?>">
                                    <?php echo htmlspecialchars($row_banner['bnn_desc']); ?>
                                </td>

                                <!-- ลิงก์ (ตัดข้อความพร้อมแสดง title) -->
                                <td class="text-truncate" style="max-width: 170px;"
                                    title="<?php echo htmlspecialchars($row_banner['bnn_link']); ?>">
                                    <?php echo htmlspecialchars($row_banner['bnn_link']); ?>
                                </td>

                                <!-- รูปภาพ -->
                                <td>
                                    <img src="<?php echo htmlspecialchars($row_banner['bnn_img']); ?>"
                                        width="80"
                                        height="80"
                                        alt="Banner Image">
                                </td>

                                <!-- สถานะ -->
                                <td>
                                    <?php echo ($row_banner['bnn_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?>
                                </td>

                                <!-- จัดการ (แก้ไข/ลบ) -->
                                <td>
                                    <a href="banner_edit.php?bnn_id=<?php echo $row_banner['bnn_id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="confirmDelete(<?php echo $row_banner['bnn_id']; ?>, '<?php echo htmlspecialchars($row_banner['bnn_name']); ?>')">
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
            $('#BannerTable').DataTable({
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

        function confirmDelete(bnnId, bnnName) {
            Swal.fire({
                title: `คุณต้องการลบแบนเนอร์: ${bnnId} ชื่อ: ${bnnName} หรือไม่?`,
                text: "หากลบแล้วจะไม่สามารถกู้คืนได้!",
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
                            bnn_id: bnnId
                        },
                        success: function(response) {
                            let res = JSON.parse(response);
                            if (res.status === "success") {
                                Swal.fire({
                                    title: "ลบสำเร็จ!",
                                    text: "แบนเนอร์ถูกลบแล้ว",
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
    </script>
</body>

</html>