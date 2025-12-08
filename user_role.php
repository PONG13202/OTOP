<?php
require_once('connection.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] === 'role_admin') {
    header("Location: back_office.php");
    exit();
}

if (!$project_connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// ฟังก์ชันตรวจสอบรหัสผ่าน
function verifyPassword($project_connect, $user_id, $password)
{
    $sql = "SELECT user_pwd FROM user WHERE user_id = ?";
    $stmt = mysqli_prepare($project_connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        // สมมติว่ารหัสผ่านถูกเก็บในรูปแบบที่เข้ารหัสด้วย password_hash
        if ($row && password_verify($password, $row['user_pwd'])) {
            return true;
        }
    }
    return false;
}

// Query สำหรับผู้ใช้ที่ยังไม่มีสิทธิ์ admin
$sql_user_admin = "SELECT user_id, user_name, user_fname, user_lname, user_email, user_img FROM user 
WHERE user_id NOT IN (SELECT user_id FROM role_admin) 
ORDER BY CONVERT(user_name USING tis620) ASC";
$result_user_admin = mysqli_query($project_connect, $sql_user_admin);

// Query สำหรับผู้ใช้ที่ยังไม่มีสิทธิ์ sup
$sql_user_sup = "SELECT user_id, user_name, user_fname, user_lname, user_email, user_img FROM user 
WHERE user_id NOT IN (SELECT user_id FROM role_sup) 
ORDER BY CONVERT(user_name USING tis620) ASC";
$result_user_sup = mysqli_query($project_connect, $sql_user_sup);

// Query สำหรับ sup
$sql_sup = "SELECT role_sup.sup_id, role_sup.user_id, role_sup.sup_show, 
user.user_name, user.user_fname, user.user_lname, 
user.user_email, user.user_img, user.user_show 
FROM role_sup
INNER JOIN user ON role_sup.user_id = user.user_id
ORDER BY CONVERT(user.user_name USING tis620) ASC";
$result_sup = mysqli_query($project_connect, $sql_sup);

// Query สำหรับ admin
$sql_role = "SELECT role_admin.admin_id, role_admin.user_id, role_admin.admin_show, 
user.user_name, user.user_fname, user.user_lname, 
user.user_email, user.user_img, user.user_show 
FROM role_admin
INNER JOIN user ON role_admin.user_id = user.user_id
ORDER BY CONVERT(user.user_name USING tis620) ASC";
$result_role = mysqli_query($project_connect, $sql_role);

// ตรวจสอบการ POST สำหรับเพิ่มสิทธิ์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'], $_POST['role'], $_POST['password'])) {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $current_user_id = $_SESSION['user_id'];

    if (verifyPassword($project_connect, $current_user_id, $password)) {
        if ($role === "role_admin") {
            $sql = "INSERT INTO role_admin (user_id, admin_show) VALUES (?, 1)";
        } elseif ($role === "role_sup") {
            $sql = "INSERT INTO role_sup (user_id, sup_show) VALUES (?, 1)";
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid role"]);
            exit();
        }

        $stmt = mysqli_prepare($project_connect, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            $execute = mysqli_stmt_execute($stmt);
            echo json_encode(["status" => $execute ? "success" : "error", "message" => $execute ? "" : mysqli_error($project_connect)]);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to prepare statement"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}

// ตรวจสอบการ POST สำหรับลบสิทธิ์ sup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sup_id'], $_POST['password'])) {
    $sup_id = intval($_POST['sup_id']);
    $password = $_POST['password'];
    $current_user_id = $_SESSION['user_id'];

    if (verifyPassword($project_connect, $current_user_id, $password)) {
        $sql = "DELETE FROM role_sup WHERE sup_id = ?";
        $stmt = mysqli_prepare($project_connect, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $sup_id);
            $execute = mysqli_stmt_execute($stmt);
            echo json_encode(["status" => $execute ? "success" : "error"]);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}

// ตรวจสอบการ POST สำหรับลบสิทธิ์ admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_id'], $_POST['password'])) {
    $admin_id = intval($_POST['admin_id']);
    $password = $_POST['password'];
    $current_user_id = $_SESSION['user_id'];

    if (verifyPassword($project_connect, $current_user_id, $password)) {
        $sql = "DELETE FROM role_admin WHERE admin_id = ?";
        $stmt = mysqli_prepare($project_connect, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $admin_id);
            $execute = mysqli_stmt_execute($stmt);
            echo json_encode(["status" => $execute ? "success" : "error"]);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_sup_id'], $_POST['sup_show'], $_POST['password'])) {
    $toggle_sup_id  = intval($_POST['toggle_sup_id']);
    $sup_show_value = intval($_POST['sup_show']);
    $password       = $_POST['password'];
    $current_user_id = $_SESSION['user_id'];

    if (verifyPassword($project_connect, $current_user_id, $password)) {
        $sql  = "UPDATE role_sup SET sup_show = ? WHERE sup_id = ?";
        $stmt = mysqli_prepare($project_connect, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $sup_show_value, $toggle_sup_id);
            $execute = mysqli_stmt_execute($stmt);
            echo json_encode(["status" => $execute ? "success" : "error"]);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}

// ตรวจสอบการ POST สำหรับเปลี่ยนสถานะ show/ไม่แสดง ของ Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_admin_id'], $_POST['admin_show'], $_POST['password'])) {
    $toggle_admin_id  = intval($_POST['toggle_admin_id']);
    $admin_show_value = intval($_POST['admin_show']);
    $password         = $_POST['password'];
    $current_user_id  = $_SESSION['user_id'];

    if (verifyPassword($project_connect, $current_user_id, $password)) {
        $sql  = "UPDATE role_admin SET admin_show = ? WHERE admin_id = ?";
        $stmt = mysqli_prepare($project_connect, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $admin_show_value, $toggle_admin_id);
            $execute = mysqli_stmt_execute($stmt);
            echo json_encode(["status" => $execute ? "success" : "error"]);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
    }
    exit();
}


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
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="main-content">

        <div class="container mt-4">
            <h2 class="text-center mb-4">เพิ่มสิทธิ์ Super Admin</h2>
            <div class="table-responsive">
                <table id="userTableSup" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>รูปภาพ</th>
                            <th>เพิ่มสิทธิ์ Super Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_user_sup)) { ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['user_name']; ?></td>
                                <td><?php echo $row['user_fname'] . " " . $row['user_lname']; ?></td>
                                <td><?php echo $row['user_email']; ?></td>
                                <td><img src="<?php echo $row['user_img']; ?>" width="80" height="80"></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="addRole(<?php echo $row['user_id']; ?>, 'role_sup')">เพิ่ม Super Admin</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="container mt-4">
            <h2 class="text-center mb-4">รายชื่อสมาชิก Super Admin</h2>
            <div class="table-responsive">
                <table id="supTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>รูปภาพ</th>
                            <th>Show</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_super = mysqli_fetch_assoc($result_sup)) { ?>
                            <tr>
                                <td><?php echo $row_super['sup_id']; ?></td>
                                <td><?php echo $row_super['user_name']; ?></td>
                                <td><?php echo $row_super['user_fname'] . " " . $row_super['user_lname']; ?></td>
                                <td><?php echo $row_super['user_email']; ?></td>
                                <td><img src="<?php echo $row_super['user_img']; ?>" alt="User Image" width="80" height="80"></td>
                                <td><?php echo ($row_super['sup_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <!-- ปุ่มเปลี่ยนสถานะแสดง/ไม่แสดง -->
                                    <?php if ($row_super['user_id'] != $_SESSION['user_id']) { ?>
                                        <?php if ($row_super['sup_show'] == 1) { ?>
                                            <button class="btn btn-secondary btn-sm" onclick="toggleShowSup(<?php echo $row_super['sup_id']; ?>, 0)">
                                                ซ่อน
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn btn-info btn-sm" onclick="toggleShowSup(<?php echo $row_super['sup_id']; ?>, 1)">
                                                แสดง
                                            </button>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php if ($row_super['sup_show'] == 1) { ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                ซ่อน
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn btn-info btn-sm" disabled>
                                                แสดง
                                            </button>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ($row_super['user_id'] != $_SESSION['user_id']) { ?>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="confirmDeleteSup(<?php echo $row_super['sup_id']; ?>, '<?php echo $row_super['user_name']; ?>')">
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>



        <div class="container mt-4">
            <h2 class="text-center mb-4">เพิ่มสิทธิ์ Admin</h2>
            <div class="table-responsive">
                <table id="userTableAdmin" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>รูปภาพ</th>
                            <th>เพิ่มสิทธิ์ Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_user_admin)) { ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['user_name']; ?></td>
                                <td><?php echo $row['user_fname'] . " " . $row['user_lname']; ?></td>
                                <td><?php echo $row['user_email']; ?></td>
                                <td><img src="<?php echo $row['user_img']; ?>" width="80" height="80"></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="addRole(<?php echo $row['user_id']; ?>, 'role_admin')">เพิ่ม Admin</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="container mt-4">
            <h2 class="text-center mb-4">รายชื่อสมาชิก Admin</h2>
            <div class="table-responsive">
                <table id="adminTable" class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>รูปภาพ</th>
                            <th>Show</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_role = mysqli_fetch_assoc($result_role)) { ?>
                            <tr>
                                <td><?php echo $row_role['admin_id']; ?></td>
                                <td><?php echo $row_role['user_name']; ?></td>
                                <td><?php echo $row_role['user_fname'] . " " . $row_role['user_lname']; ?></td>
                                <td><?php echo $row_role['user_email']; ?></td>
                                <td><img src="<?php echo $row_role['user_img']; ?>" alt="User Image" width="80" height="80"></td>
                                <td><?php echo ($row_role['admin_show'] == 1) ? "แสดง" : "ไม่แสดง"; ?></td>
                                <td>
                                    <!-- ปุ่มเปลี่ยนสถานะแสดง/ไม่แสดง -->
                                    <?php if ($row_role['admin_show'] == 1 && $row_role['user_id'] != $_SESSION['user_id']) { ?>
                                        <button class="btn btn-secondary btn-sm" onclick="toggleShowAdmin(<?php echo $row_role['admin_id']; ?>, 0)">
                                            ซ่อน
                                        </button>
                                    <?php } elseif ($row_role['admin_show'] == 0 && $row_role['user_id'] != $_SESSION['user_id']) { ?>
                                        <button class="btn btn-info btn-sm" onclick="toggleShowAdmin(<?php echo $row_role['admin_id']; ?>, 1)">
                                            แสดง
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            ซ่อน
                                        </button>
                                    <?php } ?>
                                    <?php if ($row_role['user_id'] != $_SESSION['user_id']) { ?>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="confirmDelete(<?php echo $row_role['admin_id']; ?>, '<?php echo $row_role['user_name']; ?>')">
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#userTableAdmin').DataTable({
                    "pageLength": 3, // แสดง 5 รายการต่อหน้า
                    "lengthMenu": [3, 10, 25, 50],
                    "language": {
                        "search": "ค้นหา:",
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                        "infoEmpty": "ไม่มีข้อมูลที่แสดง",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
                $('#userTableSup').DataTable({
                    "pageLength": 3, // แสดง 5 รายการต่อหน้า
                    "lengthMenu": [3, 10, 25, 50],
                    "language": {
                        "search": "ค้นหา:",
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                        "infoEmpty": "ไม่มีข้อมูลที่แสดง",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
                $('#supTable').DataTable({
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
                $('#adminTable').DataTable({
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

            function toggleShowSup(supId, newShowValue) {
                Swal.fire({
                    title: 'กรุณาใส่รหัสผ่านเพื่อยืนยัน',
                    input: 'password',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('กรุณาใส่รหัสผ่าน');
                        }
                        return password;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            data: {
                                toggle_sup_id: supId,
                                sup_show: newShowValue,
                                password: result.value
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "สำเร็จ!",
                                        text: "อัปเดตสถานะเรียบร้อย",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("ผิดพลาด!", res.message || "ไม่สามารถอัปเดตสถานะได้", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("ผิดพลาด!", "ไม่สามารถส่งข้อมูลได้", "error");
                            }
                        });
                    }
                });
            }

            function addRole(userId, role) {
                Swal.fire({
                    title: 'กรุณาใส่รหัสผ่านเพื่อยืนยัน',
                    input: 'password',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('กรุณาใส่รหัสผ่าน');
                        }
                        return password;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const password = result.value;
                        $.ajax({
                            type: "POST",
                            data: {
                                user_id: userId,
                                role: role,
                                password: password
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "สำเร็จ!",
                                        text: "เพิ่มสิทธิ์เรียบร้อยแล้ว",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("ผิดพลาด!", res.message || "ไม่สามารถเพิ่มสิทธิ์ได้", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("ผิดพลาด!", "เกิดข้อผิดพลาดในการส่งข้อมูล", "error");
                            }
                        });
                    }
                });
            }

            function confirmDeleteSup(supId, userName) {
                Swal.fire({
                    title: 'กรุณาใส่รหัสผ่านเพื่อยืนยันการลบ',
                    input: 'password',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('กรุณาใส่รหัสผ่าน');
                        }
                        return password;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const password = result.value;
                        $.ajax({
                            type: "POST",
                            data: {
                                sup_id: supId,
                                password: password
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
                                    }).then(() => {
                                        location.reload();
                                    });
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

            function toggleShowAdmin(adminId, newShowValue) {
                Swal.fire({
                    title: 'กรุณาใส่รหัสผ่านเพื่อยืนยัน',
                    input: 'password',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('กรุณาใส่รหัสผ่าน');
                        }
                        return password;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            data: {
                                toggle_admin_id: adminId,
                                admin_show: newShowValue,
                                password: result.value
                            },
                            success: function(response) {
                                let res = JSON.parse(response);
                                if (res.status === "success") {
                                    Swal.fire({
                                        title: "สำเร็จ!",
                                        text: "อัปเดตสถานะเรียบร้อย",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("ผิดพลาด!", res.message || "ไม่สามารถอัปเดตสถานะได้", "error");
                                }
                            },
                            error: function() {
                                Swal.fire("ผิดพลาด!", "ไม่สามารถส่งข้อมูลได้", "error");
                            }
                        });
                    }
                });
            }

            function confirmDelete(adminId, userName) {
                Swal.fire({
                    title: 'กรุณาใส่รหัสผ่านเพื่อยืนยันการลบ',
                    input: 'password',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    preConfirm: (password) => {
                        if (!password) {
                            Swal.showValidationMessage('กรุณาใส่รหัสผ่าน');
                        }
                        return password;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const password = result.value;
                        $.ajax({
                            type: "POST",
                            data: {
                                admin_id: adminId,
                                password: password
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
                                    }).then(() => {
                                        location.reload();
                                    });
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
mysqli_close($project_connect);
?>