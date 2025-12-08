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

// การลบประเภทสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pty_id'])) {
    $pty_id = intval($_POST['pty_id']);
    $sql = "DELETE FROM product_type WHERE pty_id = ?";
    $stmt = mysqli_prepare($project_connect, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $pty_id);
        $execute = mysqli_stmt_execute($stmt);

        if ($execute) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Unable to delete product type."]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "SQL statement preparation failed."]);
    }
    exit();
}

// การลบสินค้า (รวมถึงลบข้อมูลใน product_img และไฟล์รูปภาพ)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prd_id'])) {
    $prd_id = intval($_POST['prd_id']);

    // เริ่ม Transaction เพื่อให้การลบสมบูรณ์ทั้งหมด
    mysqli_begin_transaction($project_connect);

    try {
        // 1. ดึงข้อมูลรูปภาพจาก product_img เพื่อลบไฟล์
        $sql_images = "SELECT prd_img_name FROM product_img WHERE prd_id = ?";
        $stmt_images = mysqli_prepare($project_connect, $sql_images);
        mysqli_stmt_bind_param($stmt_images, "i", $prd_id);
        mysqli_stmt_execute($stmt_images);
        $result_images = mysqli_stmt_get_result($stmt_images);

        $image_files = [];
        while ($row = mysqli_fetch_assoc($result_images)) {
            $image_files[] = $row['prd_img_name'];
        }
        mysqli_stmt_close($stmt_images);

        // 2. ลบข้อมูลจาก product_img
        $sql_delete_img = "DELETE FROM product_img WHERE prd_id = ?";
        $stmt_img = mysqli_prepare($project_connect, $sql_delete_img);
        mysqli_stmt_bind_param($stmt_img, "i", $prd_id);
        $execute_img = mysqli_stmt_execute($stmt_img);
        mysqli_stmt_close($stmt_img);

        if (!$execute_img) {
            throw new Exception("Unable to delete data from product_img.");
        }

        // 3. ลบข้อมูลจาก product
        $sql_delete_product = "DELETE FROM product WHERE prd_id = ?";
        $stmt_product = mysqli_prepare($project_connect, $sql_delete_product);
        mysqli_stmt_bind_param($stmt_product, "i", $prd_id);
        $execute_product = mysqli_stmt_execute($stmt_product);
        mysqli_stmt_close($stmt_product);

        if (!$execute_product) {
            throw new Exception("Unable to delete data from product.");
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

// ดึงข้อมูลสินค้า (แสดงเฉพาะรูปภาพหลัก)
$sql_prd = "SELECT product.*, product_type.pty_name, pi.prd_img_name 
            FROM product 
            LEFT JOIN product_type ON product.pty_id = product_type.pty_id
            LEFT JOIN product_img pi ON product.prd_id = pi.prd_id AND pi.prd_img_show = 1
            ORDER BY CONVERT(product.prd_name USING tis620) ASC";
$result_prd = mysqli_query($project_connect, $sql_prd) or die(mysqli_error($project_connect));

// ดึงข้อมูลประเภทสินค้า
$sql_pty = "SELECT * FROM product_type ORDER BY CONVERT(pty_name USING tis620) ASC";
$result_pty = mysqli_query($project_connect, $sql_pty) or die(mysqli_error($project_connect));

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
   
        <!-- ตารางสินค้า -->
        <div class="container mt-4">
            <h2 class="text-center mb-4">รายการสินค้า</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='product_add.php'">
                <i class="fas fa-plus"></i> เพิ่มสินค้า
            </button>

            <div class="table-responsive">
                <table id="productTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อสินค้า</th>
                            <th>รายละเอียดสินค้า</th>
                            <th>ประเภทสินค้า</th>
                            <th>ราคา</th>
                            <th>รูปสินค้า</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_prd = mysqli_fetch_assoc($result_prd)) { ?>
                            <tr>
                                <td><?php echo $row_prd['prd_id']; ?></td>

                                <!-- กำหนดคลาสสำหรับ text-truncate และกำหนด style เพื่อให้เกิด ellipsis -->
                                <td class="text-truncate" style="max-width: 150px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row_prd['prd_name']); ?>
                                </td>
                                <td class="text-truncate" style="max-width: 250px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row_prd['prd_detail']); ?>
                                </td>

                                <td><?php echo htmlspecialchars($row_prd['pty_name'] ?? ''); ?></td>
                                <td><?php echo number_format($row_prd['prd_price'], 2); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($row_prd['prd_img_name'] ?? ''); ?>" width="80" height="80" alt="รูปสินค้า">
                                </td>
                                <td><?php echo ($row_prd['prd_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <a href="product_edit.php?prd_id=<?php echo $row_prd['prd_id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDeleteProduct(<?php echo $row_prd['prd_id']; ?>, '<?php echo htmlspecialchars($row_prd['prd_name']); ?>')">
                                        <i class="fas fa-trash"></i> ลบ
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

             <!-- ตารางประเภทสินค้า -->
             <div class="container mt-4">
            <h2 class="text-center mb-4">ประเภทสินค้า</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='product_type_add.php'">
                <i class="fas fa-plus"></i> เพิ่มประเภทสินค้า
            </button>

            <div class="table-responsive">
                <table id="product-typeTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อประเภทสินค้า</th>
                            <th>รายละเอียดประเภทสินค้า</th>
                            <th>รูปประเภทสินค้า</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_pty = mysqli_fetch_assoc($result_pty)) { ?>
                            <tr>
                                <td><?php echo $row_pty['pty_id']; ?></td>
                                <td><?php echo htmlspecialchars($row_pty['pty_name']); ?></td>
                                <td><?php echo htmlspecialchars($row_pty['pty_detail']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($row_pty['pty_img']); ?>" width="80" height="80" alt="รูปประเภทสินค้า"></td>
                                <td><?php echo ($row_pty['pty_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <a href="product_type_edit.php?pty_id=<?php echo $row_pty['pty_id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row_pty['pty_id']; ?>, '<?php echo htmlspecialchars($row_pty['pty_name']); ?>')">
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
            // ฟังก์ชันลบประเภทสินค้า
            window.confirmDelete = function(ptyId, ptyName) {
                Swal.fire({
                    title: `คุณต้องการลบประเภทสินค้า: ${ptyId} ชื่อ: ${ptyName} หรือไม่?`,
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
                                pty_id: ptyId
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "ลบสำเร็จ!",
                                        text: "ประเภทสินค้าถูกลบแล้ว",
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

            // ฟังก์ชันลบสินค้า
            window.confirmDeleteProduct = function(prdId, prdName) {
                Swal.fire({
                    title: `คุณต้องการลบสินค้า: ${prdId} ชื่อ: ${prdName} หรือไม่?`,
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
                                prd_id: prdId
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "ลบสำเร็จ!",
                                        text: "สินค้าและรูปภาพถูกลบแล้ว",
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

            $('#productTable').DataTable({
                // กำหนดให้เริ่มต้นแสดง 5 แถวต่อหน้า
                "pageLength": 5,
                // กำหนดตัวเลือกจำนวนรายการต่อหน้า
                "lengthMenu": [5, 10, 25, 50],
                // กำหนดการแปลภาษา
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

            $('#product-typeTable').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50],
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