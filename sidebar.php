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
?>
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
    <style>
        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            width: 240px;
            height: calc(100% - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            z-index: 1020;
            padding: 20px 10px;
            text-align: center;
        }

        .menu-btn {
            width: 90%;
            margin-bottom: 10px;
            padding: 10px;
        }

        .main-content {
            margin-top: 56px;
            margin-left: 240px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="menu-container">
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='back_office.php'">
                <i class="fas fa-home"></i> หน้าแรก
            </button>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'role_sup') { ?>
                <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='user_role.php'">
                    <i class="fas fa-edit"></i> กําหนดสิทธิ์
                </button>
            <?php } ?>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='user_show.php'">
                <i class="fas fa-users"></i> สมาชิก
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn"onclick="location.href='history_show.php'">
                <i class="fas fa-book mr-2"></i> ประวัติความเป็นมา
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='banner.php'">
                <i class="fas fa-image"></i> แบนเนอร์
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='product_show.php'">
                <i class="fa-solid fa-shop"></i> สินค้า
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='tradition_show.php'">
                <i class="fa-solid fa-mask"></i> ประเพณี
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='attraction_show.php'">
                <i class="fa-solid fa-umbrella-beach"></i> สถานที่ท่องเที่ยว
            </button>
            <button style="text-align: left;" class="btn btn-warning menu-btn" onclick="location.href='cont_show.php'">
                <i class="fas fa-address-book"></i> ข้อมูลสถานที่
            </button>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>