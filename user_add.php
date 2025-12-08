<?php
require_once('connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'role_admin' && $_SESSION['role'] !== 'role_sup')) {
    header("Location: index.php");
    exit();
}

// กำหนดตัวแปรสำหรับการอัปโหลดรูปภาพ
$target_dir = "uploads/members/";
$uploadOk = 1;

// ประมวลผลฟอร์ม
if (isset($_POST["register"]) && $_POST["register"] == "reg_ok") {
    header("Content-Type:text/html;charset=utf-8");

    $user_name = mysqli_real_escape_string($project_connect, $_POST['user_name']);
    $user_pwd = mysqli_real_escape_string($project_connect, $_POST['user_pwd']);
    $user_fname = mysqli_real_escape_string($project_connect, $_POST['user_fname']);
    $user_lname = mysqli_real_escape_string($project_connect, $_POST['user_lname']);
    $user_email = mysqli_real_escape_string($project_connect, $_POST['user_email']);
    $user_phone = mysqli_real_escape_string($project_connect, $_POST['user_phone']);
    $user_address = mysqli_real_escape_string($project_connect, $_POST['user_address']);
    $hashed_pwd = password_hash($user_pwd, PASSWORD_DEFAULT);

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $stmt = $project_connect->prepare("SELECT user_name FROM user WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('ชื่อผู้ใช้ซ้ำกัน โปรดใช้ชื่ออื่น');</script>";
    } else {
        // ใช้รูปภาพเริ่มต้น
        $user_img = "./uploads/members/user_default.jpeg";

        // อัปโหลดรูปภาพ
        if (isset($_FILES["user_img"]) && $_FILES["user_img"]["error"] == 0) {
            $check = getimagesize($_FILES["user_img"]["tmp_name"]);
            if ($check !== false) {
                $imageFileType = strtolower(pathinfo($_FILES["user_img"]["name"], PATHINFO_EXTENSION));
                if ($_FILES["user_img"]["size"] <= 20000000 && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                    // สร้างชื่อไฟล์ไม่ซ้ำ
                    $new_filename = uniqid() . "." . $imageFileType;
                    $target_file = $target_dir . $new_filename;

                    // ตรวจสอบและสร้างโฟลเดอร์
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    if (move_uploaded_file($_FILES["user_img"]["tmp_name"], $target_file)) {
                        $user_img = $target_file; // อัปเดตเส้นทางรูปภาพ
                    } else {
                        echo "<script>alert('ไม่สามารถอัปโหลดไฟล์ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์ uploads/members/');</script>";
                    }
                } else {
                    echo "<script>alert('รองรับเฉพาะไฟล์ JPG, PNG, JPEG และ GIF ขนาดไม่เกิน 20MB');</script>";
                }
            } else {
                echo "<script>alert('ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ');</script>";
            }
        }

        // เพิ่มข้อมูลสมาชิกใหม่
        $sql_script = "INSERT INTO user (user_name, user_pwd, user_fname, user_lname, user_email, user_phone, user_addr, user_img) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $project_connect->prepare($sql_script);
        $stmt->bind_param("ssssssss", $user_name, $hashed_pwd, $user_fname, $user_lname, $user_email, $user_phone, $user_address, $user_img);
        if ($stmt->execute()) {
            header("Location: user_show.php");
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มสมาชิก: " . $stmt->error . "');</script>";
        }
        $stmt->close();
        mysqli_close($project_connect);
        exit();
    }
}
?>

<?php include('navbar.php'); ?>
<?php include('sidebar.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสมาชิกใหม่</title>
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
            <a href="user_show.php" class="btn btn-outline-primary mb-4">
                <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
            </a>
            <h2 class="form-title text-center"><i class="fas fa-user-plus me-2"></i>เพิ่มสมาชิกใหม่</h2>

            <form method="POST" enctype="multipart/form-data" class="row g-4">
                <!-- Avatar Upload -->
                <div class="col-12 text-center">
                    <label for="user_img" class="d-block">
                        <img src="./uploads/members/user_default.jpeg"
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

                <!-- Username & Password -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text"
                            class="form-control"
                            id="user_name"
                            name="user_name"
                            placeholder="ชื่อผู้ใช้"
                            required>
                    </div>
                    <div id="username-feedback" class="mt-2 small"></div>
                </div>

                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password"
                            class="form-control"
                            id="user_pwd"
                            name="user_pwd"
                            placeholder="รหัสผ่าน"
                            required>
                    </div>
                </div>

                <!-- Name Section -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-id-badge"></i></span>
                        <input type="text"
                            class="form-control"
                            id="user_fname"
                            name="user_fname"
                            placeholder="ชื่อจริง"
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
                            placeholder="นามสกุล"
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
                            placeholder="อีเมล"
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
                            placeholder="เบอร์โทรศัพท์"
                            required>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <textarea class="form-control"
                            id="user_address"
                            name="user_address"
                            rows="2"
                            placeholder="ที่อยู่"
                            required></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center mt-4">
                    <button type="submit"
                        name="register"
                        id="register"
                        value="reg_ok"
                        class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-user-plus me-2"></i>เพิ่มสมาชิก
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
            let registerBtn = document.getElementById('register');

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