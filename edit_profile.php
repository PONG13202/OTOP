<?php
require_once("connection.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้จากตาราง user
$sql = "SELECT * FROM user WHERE user_id = ?";
$stmt = mysqli_prepare($project_connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: index.php");
    exit();
}

// ประมวลผลฟอร์มเมื่อมีการส่งข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cmd_ok"])) {
    $user_name = mysqli_real_escape_string($project_connect, $_POST['user_name']);
    $user_fname = mysqli_real_escape_string($project_connect, $_POST['user_fname']);
    $user_lname = mysqli_real_escape_string($project_connect, $_POST['user_lname']);
    $user_email = mysqli_real_escape_string($project_connect, $_POST['user_email']);
    $user_addr = mysqli_real_escape_string($project_connect, $_POST['user_addr']);
    $user_phone = mysqli_real_escape_string($project_connect, $_POST['user_phone']);
    $user_img = $user['user_img']; // คงค่าเริ่มต้นของรูปภาพ
    $user_pwd = $user['user_pwd']; // คงรหัสผ่านเดิมไว้เป็นค่าเริ่มต้น

    // ตรวจสอบชื่อผู้ใช้ซ้ำ (ยกเว้นตัวเอง)
    $check_sql = "SELECT user_id FROM user WHERE user_name = ? AND user_id != ?";
    $check_stmt = mysqli_prepare($project_connect, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "si", $user_name, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('ชื่อผู้ใช้ซ้ำ: ชื่อผู้ใช้นี้ถูกใช้แล้ว กรุณาเลือกชื่ออื่น');</script>";
    } else {
        // ตรวจสอบรหัสผ่านเก่าและรหัสผ่านใหม่
        $old_pwd = $_POST['old_pwd'] ?? '';
        $new_pwd = $_POST['user_pwd'] ?? '';

        if (!empty($new_pwd) || !empty($old_pwd)) { // ถ้ามีการกรอกช่องใดช่องหนึ่ง
            if (empty($new_pwd) || empty($old_pwd)) { // แต่กรอกไม่ครบทั้งสองช่อง
                echo "<script>alert('กรุณากรอกข้อมูลให้ครบ: หากต้องการเปลี่ยนรหัสผ่าน ต้องกรอกรหัสผ่านเก่าและรหัสผ่านใหม่');</script>";
            } else { // กรอกครบทั้งสองช่อง
                if (!password_verify($old_pwd, $user['user_pwd'])) {
                    echo "<script>alert('รหัสผ่านไม่ถูกต้อง: รหัสผ่านเก่าไม่ถูกต้อง กรุณากรอกใหม่');</script>";
                } else {
                    $user_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
                }
            }
        }

        // อัปโหลดรูปภาพ
        if (isset($_FILES["user_img"]) && $_FILES["user_img"]["error"] == 0) {
            $check = getimagesize($_FILES["user_img"]["tmp_name"]);
            if ($check !== false) {
                $imageFileType = strtolower(pathinfo($_FILES["user_img"]["name"], PATHINFO_EXTENSION));
                if ($_FILES["user_img"]["size"] <= 20000000 && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                    $target_dir = "uploads/members/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $new_filename = uniqid() . "." . $imageFileType;
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES["user_img"]["tmp_name"], $target_file)) {
                        $user_img = $target_file;
                    } else {
                        echo "<script>alert('อัปโหลดรูปภาพล้มเหลว: ไม่สามารถอัปโหลดรูปภาพได้ กรุณาตรวจสอบสิทธิ์ของโฟลเดอร์');</script>";
                    }
                } else {
                    echo "<script>alert('รูปภาพไม่ถูกต้อง: รองรับเฉพาะไฟล์ JPG, PNG, JPEG และ GIF ขนาดไม่เกิน 20MB');</script>";
                }
            } else {
                echo "<script>alert('รูปภาพไม่ถูกต้อง: ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ');</script>";
            }
        }

        // อัปเดตข้อมูลในฐานข้อมูล (ถ้าไม่มีข้อผิดพลาดจากรหัสผ่าน)
        if (empty($new_pwd) && empty($old_pwd) || (!empty($new_pwd) && !empty($old_pwd) && password_verify($old_pwd, $user['user_pwd']))) {
            $update_sql = "UPDATE user SET user_name=?, user_fname=?, user_lname=?, user_email=?, user_addr=?, user_phone=?, user_pwd=?, user_img=? WHERE user_id=?";
            $update_stmt = mysqli_prepare($project_connect, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssssssssi", $user_name, $user_fname, $user_lname, $user_email, $user_addr, $user_phone, $user_pwd, $user_img, $user_id);
            if (mysqli_stmt_execute($update_stmt)) {
                echo "<script>alert('อัปเดตข้อมูลเรียบร้อยแล้ว'); window.location.href = 'back_office.php';</script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาด: ไม่สามารถอัปเดตข้อมูลได้');</script>";
            }
            mysqli_stmt_close($update_stmt);
        }
    }
    mysqli_close($project_connect);
}
?>

<?php include('navbar.php'); ?>
<?php include('sidebar.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #60a5fa;
            --success-color: #22c55e;
            --error-color: #ef4444;
        }
        .main-content {
            background: #f8fafc;
            min-height: 100vh;
            padding: 2rem;
        }
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
        }
        .form-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 1rem;
        }
        .input-icon {
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem 0 0 0.5rem;
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .avatar-preview:hover {
            transform: scale(1.05);
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-card">
            <a href="back_office.php" class="btn btn-outline-primary mb-4">
                <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
            </a>
            <h2 class="form-title text-center"><i class="fas fa-user-edit me-2"></i>แก้ไขโปรไฟล์</h2>

            <form method="POST" enctype="multipart/form-data" class="row g-4">
                <!-- Avatar Upload -->
                <div class="col-12 text-center">
                    <label for="user_img" class="d-block">
                        <img src="<?php echo htmlspecialchars($user['user_img']); ?>"
                            class="avatar-preview"
                            id="avatarPreview"
                            alt="รูปโปรไฟล์">
                    </label>
                    <input type="file"
                        class="d-none"
                        id="user_img"
                        name="user_img"
                        accept="image/*">
                    <small class="text-muted d-block mt-2">คลิกเพื่ออัปโหลดรูปภาพ (สูงสุด 20MB)</small>
                </div>

                <!-- Username -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text"
                            class="form-control"
                            id="user_name"
                            name="user_name"
                            value="<?php echo htmlspecialchars($user['user_name']); ?>"
                            required>
                    </div>
                    <div id="username-feedback" class="mt-2 small"></div>
                </div>

                <!-- Name Section -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-id-badge"></i></span>
                        <input type="text"
                            class="form-control"
                            id="user_fname"
                            name="user_fname"
                            value="<?php echo htmlspecialchars($user['user_fname']); ?>"
                            required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-id-badge"></i></span>
                        <input type="text"
                            class="form-control"
                            id="user_lname"
                            name="user_lname"
                            value="<?php echo htmlspecialchars($user['user_lname']); ?>"
                            required>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email"
                            class="form-control"
                            id="user_email"
                            name="user_email"
                            value="<?php echo htmlspecialchars($user['user_email']); ?>"
                            required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-phone"></i></span>
                        <input type="tel"
                            class="form-control"
                            id="user_phone"
                            name="user_phone"
                            value="<?php echo htmlspecialchars($user['user_phone']); ?>"
                            required>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <textarea class="form-control"
                            id="user_addr"
                            name="user_addr"
                            rows="2"
                            required><?php echo htmlspecialchars($user['user_addr']); ?></textarea>
                    </div>
                </div>

                <!-- Old Password (for verification) -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password"
                            class="form-control"
                            id="old_pwd"
                            name="old_pwd"
                            placeholder="รหัสผ่านเก่า (หากต้องการเปลี่ยนรหัสผ่าน)">
                    </div>
                </div>

                <!-- New Password -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password"
                            class="form-control"
                            id="user_pwd"
                            name="user_pwd"
                            placeholder="รหัสผ่านใหม่ (เว้นว่างไว้หากไม่เปลี่ยน)">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center mt-4">
                    <button type="submit"
                        name="cmd_ok"
                        id="cmd_ok"
                        class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-user-edit me-2"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image Preview
        document.addEventListener("DOMContentLoaded", function() {
            const avatarPreview = document.getElementById('avatarPreview');
            const userImgInput = document.getElementById('user_img');

            userImgInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Username Check
        document.addEventListener("DOMContentLoaded", function() {
            let usernameInput = document.getElementById('user_name');
            let feedback = document.getElementById('username-feedback');
            let registerBtn = document.getElementById('cmd_ok');

            usernameInput.addEventListener('input', function() {
                let username = this.value.trim();
                if (username === "") {
                    feedback.innerHTML = "";
                    registerBtn.disabled = true;
                    return;
                }

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "check_username.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        feedback.innerHTML = xhr.responseText;
                        registerBtn.disabled = xhr.responseText.includes("ชื่อผู้ใช้นี้ถูกใช้แล้ว");
                    }
                };
                xhr.send("user_name=" + encodeURIComponent(username));
            });
        });
    </script>
</body>
</html>