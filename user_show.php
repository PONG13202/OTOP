<?php
require_once('connection.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    if (isset($_SESSION['role']) && $_SESSION['role'] == 'role_admin') {
        $sql_check_sup = "SELECT user_id FROM role_sup WHERE user_id = ?";
        $stmt_check = mysqli_prepare($project_connect, $sql_check_sup);
        mysqli_stmt_bind_param($stmt_check, "i", $user_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            echo json_encode(["status" => "error", "message" => "ไม่สามารถลบผู้ใช้ที่มี role_sup ได้"]);
            mysqli_stmt_close($stmt_check);
            mysqli_close($project_connect);
            exit();
        }
        mysqli_stmt_close($stmt_check);
    }

    // ลบข้อมูลจาก role_admin
    $sql_delete_admin = "DELETE FROM role_admin WHERE user_id = ?";
    $stmt_admin = mysqli_prepare($project_connect, $sql_delete_admin);
    if ($stmt_admin) {
        mysqli_stmt_bind_param($stmt_admin, "i", $user_id);
        mysqli_stmt_execute($stmt_admin);
        mysqli_stmt_close($stmt_admin);
    }

    // ลบข้อมูลจาก role_sup
    $sql_delete_sup = "DELETE FROM role_sup WHERE user_id = ?";
    $stmt_sup = mysqli_prepare($project_connect, $sql_delete_sup);
    if ($stmt_sup) {
        mysqli_stmt_bind_param($stmt_sup, "i", $user_id);
        mysqli_stmt_execute($stmt_sup);
        mysqli_stmt_close($stmt_sup);
    }

    // ลบผู้ใช้จากตาราง user
    $sql_delete_user = "DELETE FROM user WHERE user_id = ?";
    $stmt_user = mysqli_prepare($project_connect, $sql_delete_user);
    if ($stmt_user) {
        mysqli_stmt_bind_param($stmt_user, "i", $user_id);
        $execute = mysqli_stmt_execute($stmt_user);
        if ($execute) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        mysqli_stmt_close($stmt_user);
    } else {
        echo json_encode(["status" => "error"]);
    }
    mysqli_close($project_connect);
    exit();
}

$sql_user = "SELECT * FROM user ORDER BY CONVERT(user_name USING tis620) ASC";
$result_user = mysqli_query($project_connect, $sql_user) or die(mysqli_error($project_connect));

$sql_sup_ids = "SELECT user_id FROM role_sup";
$result_sup_ids = mysqli_query($project_connect, $sql_sup_ids);
$sup_user_ids = [];
while ($row = mysqli_fetch_assoc($result_sup_ids)) {
    $sup_user_ids[] = $row['user_id'];
}

$sql_admin_ids = "SELECT user_id FROM role_admin";
$result_admin_ids = mysqli_query($project_connect, $sql_admin_ids);
$admin_user_ids = [];
while ($row = mysqli_fetch_assoc($result_admin_ids)) {
    $admin_user_ids[] = $row['user_id'];
}

mysqli_close($project_connect);
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">

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
            <h2 class="text-center mb-4">รายชื่อสมาชิก</h2>
            <button type="button" class="btn btn-primary mb-3" onclick="location.href='user_add.php'">
                <i class="fas fa-plus"></i> เพิ่มสมาชิก
            </button>

            <div class="table-responsive">
                <table id="userTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>ที่อยู่</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>รูปภาพ</th>
                            <th>Show</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_user = mysqli_fetch_assoc($result_user)) {
                            $is_sup = in_array($row_user['user_id'], $sup_user_ids);
                            $is_admin = in_array($row_user['user_id'], $admin_user_ids);
                        ?>
                            <tr>
                                <td><?php echo $row_user['user_id']; ?></td>
                                <td><?php echo $row_user['user_name']; ?></td>
                                <td><?php echo $row_user['user_fname'] . " " . $row_user['user_lname']; ?></td>
                                <td><?php echo $row_user['user_email']; ?></td>
                                <td><?php echo $row_user['user_addr']; ?></td>
                                <td><?php echo $row_user['user_phone']; ?></td>
                                <td><img src="<?php echo $row_user['user_img']; ?>" alt="User Image" width="80" height="80"></td>
                                <td><?php echo ($row_user['user_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'role_admin' && $row_user['user_id'] === $_SESSION['user_id']) { ?>
                                        <a href="user_edit.php?user_id=<?php echo $row_user['user_id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </a>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'role_admin' && ($is_admin && $row_user['user_id'] !== $_SESSION['user_id'])) { ?>
                                        <button class="btn btn-warning btn-sm" disabled>
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'role_sup') { ?>
                                        <?php if ($is_sup) { ?>
                                            <button class="btn btn-warning btn-sm" disabled>
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </button>
                                            <?php if ($row_user['user_id'] != $_SESSION['user_id']) { ?>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row_user['user_id']; ?>, '<?php echo $row_user['user_name']; ?>')">
                                                    <i class="fas fa-trash"></i> ลบ
                                                </button>
                                            <?php } else { ?>
                                                <button class="btn btn-danger btn-sm" disabled>
                                                    <i class="fas fa-trash"></i> ลบ
                                                </button>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <a href="user_edit.php?user_id=<?php echo $row_user['user_id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </a>
                                            <?php if ($row_user['user_id'] != $_SESSION['user_id']) { ?>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row_user['user_id']; ?>, '<?php echo $row_user['user_name']; ?>')">
                                                    <i class="fas fa-trash"></i> ลบ
                                                </button>
                                            <?php } else { ?>
                                                <button class="btn btn-danger btn-sm" disabled>
                                                    <i class="fas fa-trash"></i> ลบ
                                                </button>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php if ($is_sup) { ?>
                                            <button class="btn btn-warning btn-sm" disabled>
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </button>
                                            <button class="btn btn-danger btn-sm" disabled>
                                                <i class="fas fa-trash"></i> ลบ
                                            </button>
                                        <?php } else { ?>
                                            <a href="user_edit.php?user_id=<?php echo $row_user['user_id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </a>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row_user['user_id']; ?>, '<?php echo $row_user['user_name']; ?>')">
                                                <i class="fas fa-trash"></i> ลบ
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
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
            $('#userTable').DataTable({
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

        function confirmDelete(userId, userName) {
            Swal.fire({
                title: `คุณต้องการลบ ID: ${userId} ชื่อ: ${userName} หรือไม่?`,
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
                            user_id: userId
                        },
                        success: function(response) {
                            let res = JSON.parse(response);
                            if (res.status === "success") {
                                Swal.fire({
                                    title: "ลบสำเร็จ!",
                                    text: "ข้อมูลถูกลบแล้ว",
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire("เกิดข้อผิดพลาด!", res.message || "ไม่สามารถลบข้อมูลได้", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("เกิดข้อผิดพลาด!", "ไม่สามารถลบข้อมูลได้", "error");
                        }
                    });
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>