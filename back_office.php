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
$sql_script = "SELECT * FROM user ";
$result = mysqli_query($project_connect, $sql_script) or die(mysqli_connect_error());
$row_result = mysqli_fetch_assoc($result);

$sql_sup = "SELECT * FROM role_sup WHERE user_id = " . $_SESSION['user_id'];
$result_sup = mysqli_query($project_connect, $sql_sup) or die(mysqli_connect_error());
$row_sup = mysqli_fetch_assoc($result_sup);

$sql_admin = "SELECT * FROM role_admin WHERE user_id = " . $_SESSION['user_id'];
$result_admin = mysqli_query($project_connect, $sql_admin) or die(mysqli_connect_error());
$row_admin = mysqli_fetch_assoc($result_admin);

$sql_sup_all = "SELECT u.user_id, u.user_fname, u.user_lname, u.user_phone, u.user_email
FROM role_sup rs
INNER JOIN user u ON rs.user_id = u.user_id
WHERE rs.sup_show = 1";
$result_sup_all = mysqli_query($project_connect, $sql_sup_all) or die(mysqli_connect_error());




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
</head>

<body>

    <div class="main-content">

        <div class="container mt-4">
            <h2>ยินดีต้อนรับสู่ Dashboard</h2>
            <p>ที่นี่คุณสามารถจัดการข้อมูลต่างๆ ของคุณได้</p>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            รายละเอียด
                        </div>
                        <div class="card-body">
                            <p>ข้อมูลผู้ใช้: <strong><?php echo $row_result['user_fname'] . " " . $row_result['user_lname']; ?></strong></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            สิทธิ์ของคุณ
                        </div>
                        <div class="card-body">
                            <?php if ($row_sup && $row_admin) { ?>
                                <p>คุณมีสิทธิ์เป็น <strong>Super Admin</strong> และ <strong>Admin</strong></p>
                            <?php } elseif ($row_sup) { ?>
                                <p>คุณมีสิทธิ์เป็น <strong>Super Admin</strong></p>
                            <?php } elseif ($row_admin) { ?>
                                <p>คุณมีสิทธิ์เป็น <strong>Admin</strong></p>
                            <?php } else { ?>
                                <p>คุณไม่มีสิทธิ์ใดๆ</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-white">
                            ติดต่อผู้ดูแลระบบ 
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($result_sup_all) > 0) {
                                while ($row_sup_all = mysqli_fetch_assoc($result_sup_all)) {
                            ?>
                                    <p>ชื่อผู้ดูแลระบบ: <strong><?php echo $row_sup_all['user_fname'] . " " . $row_sup_all['user_lname']; ?></strong></p>
                                    <p>เบอร์โทรศัพท์: <strong><?php echo $row_sup_all['user_phone']; ?></strong></p>
                                    <p>อีเมล์: <strong><?php echo $row_sup_all['user_email']; ?></strong></p>
                                    <hr>
                                <?php
                                }
                            } else {
                                ?>
                                <p>ไม่มีข้อมูลผู้ดูแลระบบ</p>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>