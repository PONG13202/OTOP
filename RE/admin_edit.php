<?php
require_once("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] === 'role_admin') {
    header("Location: back_office.php");
    exit();
}
$admin_id = isset($_GET['admin_id']) ? intval($_GET['admin_id']) : 0;
$sql_role = "SELECT role_admin.admin_id, role_admin.user_id, role_admin.admin_show, 
                    user.user_name, user.user_fname, user.user_lname, 
                    user.user_email, user.user_img, user.user_show 
             FROM role_admin
             INNER JOIN user ON role_admin.user_id = user.user_id
             WHERE role_admin.admin_id = $admin_id";
$result_role = mysqli_query($project_connect, $sql_role) or die(mysqli_connect_error());
$row_role = mysqli_fetch_assoc($result_role);

$sql_users = "SELECT user_id, user_name FROM user ORDER BY user_name ASC";
$result_users = mysqli_query($project_connect, $sql_users) or die(mysqli_connect_error());

if ((isset($_POST["cmd_ok"])) && ($_POST["cmd_ok"] == "ตกลง")) {
    header("Content-Type:text/html;charset=utf-8");

    $sql_update_role = "UPDATE role_admin SET user_id = '" . $_POST['user_id'] . "', admin_show = '" . $_POST['admin_show'] . "' WHERE admin_id = '" . $_POST['admin_id'] . "'";

    $result_update = mysqli_query($project_connect, $sql_update_role) or die(mysqli_connect_error());

    mysqli_close($project_connect);
    header("Location: user_role.php");
}
?>
<?php include('navbar.php'); ?>
<?php include('sidebar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ดูแลระบบ</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Kanit", sans-serif;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background-color: #4e73df;
            color: #fff;
            border-radius: 16px 16px 0 0 !important;
            text-align: center;
            padding: 20px 0;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: #4e73df;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background-color: #3c5ed9;
        }

        .btn-secondary {
            border-radius: 10px;
            padding: 10px 20px;
        }

        .card-body {
            padding: 2rem;
        }
    </style>
</head>

<body>
<div class="main-content">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-header">
                <h2 class="mb-0">แก้ไขข้อมูลผู้ดูแลระบบ</h2>
            </div>
            <div class="card-body">
                <form id="role-form" method="POST">
                    <input
                        type="hidden"
                        id="admin_id"
                        name="admin_id"
                        value="<?php echo $row_role['admin_id']; ?>">

                    <div class="mb-3">
                        <label for="user_name" class="form-label">Username</label>
                        <input
                            type="hidden"
                            id="user_id"
                            name="user_id"
                            value="<?php echo $row_role['user_id']; ?>">
                        <input
                            type="text"
                            class="form-control"
                            id="user_name"
                            name="user_name"
                            value="<?php echo $row_role['user_name']; ?>"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label for="admin_show" class="form-label">สถานะ</label>
                        <select
                            name="admin_show"
                            id="admin_show"
                            class="form-control">
                            <option
                                value="1"
                                <?php echo ($row_role['admin_show'] == 1) ? 'selected' : ''; ?>>
                                แสดง
                            </option>
                            <option
                                value="0"
                                <?php echo ($row_role['admin_show'] == 0) ? 'selected' : ''; ?>>
                                ซ่อน
                            </option>
                        </select>
                    </div>

                    <div class="text-center mt-4">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            name="cmd_ok"
                            value="ตกลง">
                            บันทึก
                        </button>
                        <a
                            href="user_role.php"
                            class="btn btn-secondary ms-2">
                            ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>