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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cont_id'])) {
    $cont_id = $_POST['cont_id'];

    $sql_check = "SELECT cont_id FROM contact WHERE cont_id = ?";
    $stmt_check = mysqli_prepare($project_connect, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $cont_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        $sql_delete = "DELETE FROM contact WHERE cont_id = ?";
        $stmt_delete = mysqli_prepare($project_connect, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $cont_id);

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
    exit();
}

// ดึงข้อมูล
$contacts = mysqli_query($project_connect, "SELECT * FROM contact ORDER BY cont_name ASC");
$locations = mysqli_query($project_connect, "SELECT * FROM locat ORDER BY loca_name ASC");
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลติดต่อและสถานที่</title>
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
                <h2 class="mb-4">
                    จัดการสถานที่
                </h2>
                <table class="table table-striped" id="locationTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อสถานที่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($locations)): ?>
                            <tr>
                                <td><?= $row['loca_id'] ?></td>

                                <!-- เพิ่มการตัดข้อความหากยาวเกินกำหนด -->
                                <td class="text-truncate" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    title="<?php echo htmlspecialchars($row['loca_name']); ?>">
                                    <?= htmlspecialchars($row['loca_name']) ?>
                                </td>
                                <td>
                                    <a href="location_edit.php?loca_id=<?php echo $row['loca_id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
            <!-- ส่วนจัดการ Contact -->
            <!-- ส่วนจัดการข้อมูลติดต่อ -->
            <section class="mt-5">
                <!-- เพิ่มปุ่มเพิ่มข้อมูลก่อนตาราง -->
                <h2 class="mb-4">
                    จัดการช่องทางการติดต่อ
                </h2>
                <div class="mb-3">
                    <a href="cont_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> เพิ่มข้อมูล
                    </a>
                </div>

                <table class="table table-striped" id="contactTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ช่องทางการติดต่อ</th>
                            <th>ลิงก์</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_con = mysqli_fetch_assoc($contacts)): ?>
                            <tr>
                                <td><?= $row_con['cont_id'] ?></td>

                                <!-- เพิ่ม text-truncate และกำหนด width/overflow เพื่อให้ตัดข้อความ -->
                                <td class="text-truncate" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    title="<?php echo htmlspecialchars($row_con['cont_name']); ?>">
                                    <?= htmlspecialchars($row_con['cont_name']) ?>
                                </td>

                                <td><?= htmlspecialchars($row_con['cont_link']) ?></td>
                                <td>
                                    <a href="cont_edit.php?cont_id=<?php echo $row_con['cont_id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="confirmDelete(<?php echo $row_con['cont_id']; ?>, '<?php echo htmlspecialchars($row_con['cont_name']); ?>')">
                                        <i class="fas fa-trash"></i> ลบ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- ส่วนจัดการ Location -->
        </div>

        <script>
            $(document).ready(function() {
                $('#contactTable, #locationTable').DataTable({
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


            function confirmDelete(cont_id, contName) {
                Swal.fire({
                    title: `คุณต้องการลบลบข้อมูลติดต่อ: ID: ${cont_id} ชื่อ: ${contName} หรือไม่?`,
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
                                cont_id: cont_id
                            },

                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "ลบสำเร็จ!",
                                        text: "ข้อมูลติดต่อถูกลบแล้ว",
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