<?php
require_once("connection.php");
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

$sup_id = isset($_GET['sup_id']) ? intval($_GET['sup_id']) : 0;

if ($sup_id === 0) {
    echo "ไม่พบข้อมูลผู้ดูแลระบบ";
    exit();
}

$sql_sup = "SELECT role_sup.sup_id, role_sup.user_id, role_sup.sup_show, 
user.user_name, user.user_fname, user.user_lname, 
user.user_email, user.user_img, user.user_show 
FROM role_sup
INNER JOIN user ON role_sup.user_id = user.user_id
WHERE role_sup.sup_id = $sup_id
ORDER BY CONVERT(user.user_name USING tis620) ASC";

$result_sup = mysqli_query($project_connect, $sql_sup);

if ($result_sup) {
    $row_sup = mysqli_fetch_assoc($result_sup);
} else {
    echo "ไม่สามารถดึงข้อมูลจากฐานข้อมูลได้: " . mysqli_error($project_connect);
    exit();
}

$sql_users = "SELECT user_id, user_name FROM user ORDER BY user_name ASC";
$result_users = mysqli_query($project_connect, $sql_users) or die(mysqli_connect_error());

// ตรวจสอบว่าเมื่อกด "ตกลง" จะทำการบันทึกข้อมูลใหม่หรือไม่
if ((isset($_POST["cmd_ok"])) && ($_POST["cmd_ok"] == "ตกลง")) {
    header("Content-Type:text/html;charset=utf-8");
    $sql_update_role = "UPDATE role_sup 
                        SET user_id = '" . $_POST['user_id'] . "', 
                            sup_show = '" . $_POST['sup_show'] . "' 
                        WHERE sup_id = '" . $_POST['sup_id'] . "'";
    $result_update = mysqli_query($project_connect, $sql_update_role) or die(mysqli_connect_error());
    mysqli_close($project_connect);
    header("Location: user_role.php");
    exit();
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
            background-color: #f1f2f6;
            font-family: "Kanit", sans-serif;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px;
            text-align: center;
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
            border-color: #4e73df;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: #3a5fd9;
            border-color: #3a5fd9;
        }
        .btn-secondary {
            border-radius: 10px;
        }
        .table th, 
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-header">
                <h2>แก้ไขข้อมูลผู้ดูแลระบบ</h2>
            </div>
            <div class="card-body">
                <form id="role-form" method="POST">
                    <input type="hidden" id="sup_id" name="sup_id" value="<?php echo $row_sup['sup_id']; ?>">
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Username</label>
                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $row_sup['user_id']; ?>">
                        <input 
                            type="text" 
                            class="form-control" 
                            id="user_name" 
                            name="user_name" 
                            value="<?php echo $row_sup['user_name']; ?>" 
                            readonly
                        >
                    </div>
                    <div class="mb-3">
                        <label for="admin_show" class="form-label">สถานะ</label>
                        <select name="sup_show" id="admin_show" class="form-control">
                            <option value="1" <?php echo ($row_sup['sup_show'] == 1) ? 'selected' : ''; ?>>แสดง</option>
                            <option value="0" <?php echo ($row_sup['sup_show'] == 0) ? 'selected' : ''; ?>>ซ่อน</option>
                        </select>
                    </div>
                    <div class="text-center mt-4">
                        <button 
                            type="submit" 
                            class="btn btn-primary px-4" 
                            name="cmd_ok" 
                            value="ตกลง"
                        >
                            บันทึก
                        </button>
                        <a href="user_role.php" class="btn btn-secondary px-4 ms-2">
                            ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>