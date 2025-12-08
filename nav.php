<?php
require_once('connection.php');

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// จัดการ logout
if (isset($_POST['cmd_out']) && $_POST['cmd_out'] == 'cmd_out') {
    // ล้าง session ทั้งหมด
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// สร้างปุ่มสำหรับ navigation bar
$buttons = '<div class="d-flex flex-nowrap gap-3 align-items-center justify-content-end">';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $buttons .= '<div class="welcome-message">
                    <span>สวัสดี <strong>' . htmlspecialchars($_SESSION['fname'] ?? '') . ' ' . htmlspecialchars($_SESSION['lname'] ?? '') . '</strong></span>
                 </div>
                 <a href="profile_edit.php" class="btn btn-outline-primary rounded-pill">
                     <i class="fas fa-user-edit me-1"></i> แก้ไขโปรไฟล์
                 </a>';

    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['role_admin', 'role_sup'])) {
        $buttons .= '<a href="back_office.php" class="btn btn-primary btn-admin">
                        <i class="fas fa-cog me-1"></i> จัดการระบบ
                     </a>';
    }

    $buttons .= '<form method="POST" class="d-inline" style="margin-top: 17px;">
    <button type="submit" name="cmd_out" value="cmd_out" class="btn btn-outline-danger btn-logout">
        <i class="fas fa-sign-out-alt me-1"></i> ออกจากระบบ
    </button>
</form>';
} else {
    $buttons .= '<a href="login.php" class="btn btn-outline-primary btn-login">
                    <i class="fas fa-sign-in-alt me-1"></i> เข้าสู่ระบบ
                 </a>';
}

$buttons .= '</div';
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="uploads/assets/logo (6).png" alt="Logo" style="height: 42px; margin-right: 10px;" />
            <span style="font-family: 'Prompt', sans-serif; font-weight: 600; color: #555; font-size: 18px;">พระนครศรีอยุธยา</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php" style="font-weight: 500;">
                        <i class="fas fa-home me-1"></i> หน้าแรก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="attraction.php" style="font-weight: 500;">
                        <i class="fas fa-map-marker-alt me-1"></i> สถานที่ท่องเที่ยว
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tradition.php" style="font-weight: 500;">
                        <i class="fas fa-landmark me-1"></i> ประเพณี
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="product.php" style="font-weight: 500;">
                        <i class="fas fa-shopping-bag me-1"></i> สินค้า
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php" style="font-weight: 500;">
                        <i class="fas fa-envelope me-1"></i> ติดต่อเรา
                    </a>
                </li>
            </ul>
            <?php echo $buttons; ?>
        </div>
    </div>
</nav>