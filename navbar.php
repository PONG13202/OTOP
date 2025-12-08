<?php
require_once("connection.php");
// ตรวจสอบการกดปุ่มออกจากระบบ
if (isset($_POST['cmd_out']) && $_POST['cmd_out'] === 'cmd_out') {
    // ล้างค่าที่เกี่ยวกับผู้ใช้จาก session
    $_SESSION['user_id'] = "";
    $_SESSION['fname'] = "";
    $_SESSION['lname'] = "";
    $_SESSION['role'] = "";
    session_unset();
    // หลังออกจากระบบ อาจ redirect ไปหน้าใดหน้าหนึ่ง
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// สร้างตัวแปร $buttons เพื่อใช้แสดงปุ่มบน navbar
$buttons = '<div class="d-flex flex-nowrap gap-3 align-items-center justify-content-end">';

// ถ้ามีการเข้าสู่ระบบอยู่
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // แสดงข้อความต้อนรับ
    $buttons .= '
        <div class="bg-light p-2 rounded-pill text-nowrap">
            สวัสดี <strong>' . htmlspecialchars($_SESSION['fname'] . " " . $_SESSION['lname']) . '</strong>
        </div>
    ';
    // <img src="' . $_SESSION['user_img'] . '" alt="รูปภาพผู้ใช้" width="30" height="30" class="rounded-circle me-2">
    // ปุ่มเข้าไปแก้ไขข้อมูลโปรไฟล์
    $buttons .= '
        <a href="edit_profile.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-user-edit me-1"></i> แก้ไขข้อมูลโปรไฟล์
        </a>
    ';

    // ปุ่มออกจากระบบ
    $buttons .= '
        <form method="POST" class="m-0">
            <button type="submit" name="cmd_out" value="cmd_out" class="btn btn-outline-danger rounded-pill">
                <i class="fas fa-sign-out-alt me-1"></i> ออกจากระบบ
            </button>
        </form>
    ';
} else {
    // หากต้องการให้เปิดเข้าได้เฉพาะผู้มี session อาจขึ้น alert หรือจะ redirect ไปหน้าอื่นตามต้องการ
    header("Location: index.php");
    exit();
}

$buttons .= '</div>';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>Back Office - Ayutthaya</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" />
    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Prompt', sans-serif !important;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .navbar-glass {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(6px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-glass .navbar-brand,
        .navbar-glass .nav-link {
            color: #333;
        }

        .navbar-glass .nav-link:hover {
            color: #0d6efd;
        }

        .rounded-pill {
            border-radius: 50px !important;
        }

        .text-nowrap {
            white-space: nowrap;
        }

        .d-flex.flex-nowrap {
            flex-wrap: nowrap !important;
        }
    </style>
</head>

<body>
    <!-- เริ่มต้น Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="back_office.php">
                <img
                    src="uploads/assets/logo (6).png"
                    alt="Logo"
                    style="height: 40px; width: 40px;"
                    class="rounded-circle me-2">
                <span>จังหวัดพระนครศรีอยุธยา</span>
            </a>

            <!-- ปุ่ม Toggle บนมือถือ -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMain"
                aria-controls="navbarMain"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ส่วนเมนู Navbar -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- เปลี่ยนจาก me-auto เป็น ms-auto เพื่อเลื่อนไปทางขวา -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i> หน้าแรก
                        </a>
                    </li>
                    <!-- เพิ่มเมนูอื่น ๆ ตามต้องการ -->
                </ul>

                <!-- ส่วนปุ่มต้อนรับ / edit_profile / logout -->
                <?php echo $buttons; ?>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>