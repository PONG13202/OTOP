<?php
require_once('connection.php');

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$target_dir = "uploads/members/";
$target_file = $target_dir . basename($_FILES["user_img"]["name"] ?? '');
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


if ((isset($_POST["register"])) && ($_POST["register"] == "reg_ok")) {
    header("Content-Type:text/html;charset=utf-8");

    $user_name = mysqli_real_escape_string($project_connect, $_POST['user_name']);
    $user_pwd = mysqli_real_escape_string($project_connect, $_POST['user_pwd']);
    $user_fname = mysqli_real_escape_string($project_connect, $_POST['user_fname']);
    $user_lname = mysqli_real_escape_string($project_connect, $_POST['user_lname']);
    $user_email = mysqli_real_escape_string($project_connect, $_POST['user_email']);
    $user_phone = mysqli_real_escape_string($project_connect, $_POST['user_phone']);
    $user_address = mysqli_real_escape_string($project_connect, $_POST['user_address']);
    $hashed_pwd = password_hash($user_pwd, PASSWORD_DEFAULT);



    $stmt = $project_connect->prepare("SELECT user_name FROM user WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('ชื่อผู้ใช้ซ้ำกัน โปรดใช้ชื่ออื่น');</script>";
    } else {
        $user_img = "./uploads/members/user_default.jpeg";

        if (isset($_FILES["user_img"]) && $_FILES["user_img"]["error"] == 0) {
            $check = getimagesize($_FILES["user_img"]["tmp_name"]);
            if ($check !== false) {
                if ($_FILES["user_img"]["size"] <= 20000000 && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                    if (move_uploaded_file($_FILES["user_img"]["tmp_name"], $target_file)) {
                        $user_img = $target_file;
                    } else {
                        echo "<script>alert('ไม่สามารถอัปโหลดไฟล์ได้');</script>";
                    }
                } else {
                    echo "<script>alert('รองรับเฉพาะไฟล์ JPG, PNG, JPEG และ GIF ขนาดไม่เกิน 20MB');</script>";
                }
            } else {
                echo "<script>alert('ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ');</script>";
            }
        }

        $sql_script = "INSERT INTO user (user_name, user_pwd, user_fname, user_lname, user_email, user_phone, user_addr, user_img) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $project_connect->prepare($sql_script);
        $stmt->bind_param("ssssssss", $user_name, $hashed_pwd, $user_fname, $user_lname, $user_email, $user_phone, $user_address, $user_img);
        $stmt->execute();

        echo "<script>
            alert('สมัครสมาชิกสำเร็จ ไปยังหน้าล็อกอิน');
            window.location = 'login.php';
        </script>";
    }

    $stmt->close();
    mysqli_close($project_connect);
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <!-- เรียกใช้ Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- เรียกใช้ Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- สไตล์เพิ่มเติมเพื่อปรับแต่งรูปแบบ -->
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #60a5fa;
            --success: #22c55e;
            --error: #ef4444;
        }

        .auth-wrapper {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            padding: 2rem;
        }

        .auth-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 2.5rem;
            position: relative;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }

        .input-group-custom {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            cursor: pointer;
            margin: 0 auto 1.5rem;
            display: block;
            transition: all 0.3s ease;
        }

        .avatar-preview:hover {
            transform: scale(1.05);
        }

        .btn-auth {
            width: 100%;
            padding: 1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: var(--primary);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .auth-link {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .auth-wrapper {
                padding: 1rem;
            }

            .auth-card {
                padding: 1.5rem;
            }
        }

        .back-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 576px) {
            .back-btn {
                top: 0.5rem;
                left: 0.5rem;
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>

    <!-- JavaScript ส่วนตรวจสอบชื่อผู้ใช้ซ้ำ -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let usernameInput = document.getElementById('user_name');
            let feedback = document.getElementById('username-feedback');
            let registerBtn = document.getElementById('register');

            if (!usernameInput || !feedback || !registerBtn) {
                console.error("Element not found! ตรวจสอบ ID ของ input และ button ใน HTML");
                return;
            }

            usernameInput.addEventListener('input', function() {
                let username = this.value.trim(); // ตัดช่องว่างออก

                if (username === "") {
                    feedback.innerHTML = "";
                    registerBtn.disabled = true; // ปิดปุ่มสมัคร หากยังไม่มีการกรอกชื่อผู้ใช้
                    return;
                }

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "check_username.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        feedback.innerHTML = xhr.responseText;

                        // เช็คว่าข้อความตอบกลับมีคำว่า "ถูกใช้แล้ว" หรือไม่
                        if (xhr.responseText.includes("ชื่อผู้ใช้นี้ถูกใช้แล้ว")) {
                            registerBtn.disabled = true; // ปิดปุ่มสมัคร
                        } else {
                            registerBtn.disabled = false; // เปิดปุ่มสมัคร
                        }
                    }
                };
                xhr.send("user_name=" + encodeURIComponent(username));
            });
        });
    </script>

    <!-- JavaScript ส่วนตรวจสอบความตรงกันของรหัสผ่าน -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let passwordInput = document.getElementById('user_pwd');
            let confirmPasswordInput = document.getElementById('confirm_pwd');
            let feedback = document.getElementById('password-feedback');
            let registerBtn = document.getElementById('register');

            if (!passwordInput || !confirmPasswordInput || !feedback || !registerBtn) {
                console.error("Element not found! ตรวจสอบ ID ของ input และ button ใน HTML");
                return;
            }

            passwordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validatePassword);

            function validatePassword() {
                let password = passwordInput.value;
                let confirmPassword = confirmPasswordInput.value;

                if (confirmPassword === "") {
                    feedback.innerHTML = '';
                    registerBtn.disabled = false; // เปิดปุ่มสมัคร
                    return;
                }

                if (password !== confirmPassword) {
                    feedback.innerHTML = '<span style="color: red;">รหัสผ่านไม่ตรงกัน</span>';
                    registerBtn.disabled = true; // ปิดปุ่มสมัคร
                } else {
                    feedback.innerHTML = '<span style="color: green;">รหัสผ่านตรงกัน</span>';
                    registerBtn.disabled = false; // เปิดปุ่มสมัคร
                }
            }
        });
    </script>
    <div class="auth-wrapper">
        <a href="login.php" class="btn btn-outline-primary back-btn">
            <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
        </a>
        <div class="auth-card">
            <div class="auth-header">
                <label for="user_img">
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
                <h1 class="auth-title"><i class="fas fa-user-plus me-2"></i>สมัครสมาชิก</h1>
                <p class="auth-subtitle">กรุณากรอกข้อมูลให้ครบถ้วน</p>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Username -->
                    <div class="col-12">
                        <div class="input-group-custom">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text"
                                class="form-control-custom"
                                id="user_name"
                                name="user_name"
                                placeholder="ชื่อผู้ใช้"
                                required>
                        </div>
                        <div id="username-feedback" class="small ms-2"></div>
                    </div>

                    <!-- Password -->
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password"
                                class="form-control-custom"
                                id="user_pwd"
                                name="user_pwd"
                                placeholder="รหัสผ่าน"
                                required>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar"></div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-check-circle input-icon"></i>
                            <input type="password"
                                class="form-control-custom"
                                id="confirm_pwd"
                                name="confirm_pwd"
                                placeholder="ยืนยันรหัสผ่าน"
                                required>
                        </div>
                        <div id="password-feedback" class="small ms-2"></div>
                    </div>

                    <!-- Name -->
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-id-badge input-icon"></i>
                            <input type="text"
                                class="form-control-custom"
                                id="user_fname"
                                name="user_fname"
                                placeholder="ชื่อจริง"
                                required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-id-badge input-icon"></i>
                            <input type="text"
                                class="form-control-custom"
                                id="user_lname"
                                name="user_lname"
                                placeholder="นามสกุล"
                                required>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email"
                                class="form-control-custom"
                                id="user_email"
                                name="user_email"
                                placeholder="อีเมล"
                                required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel"
                                class="form-control-custom"
                                id="user_phone"
                                name="user_phone"
                                placeholder="เบอร์โทรศัพท์"
                                required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <div class="input-group-custom">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <textarea class="form-control-custom"
                                id="user_address"
                                name="user_address"
                                rows="2"
                                placeholder="ที่อยู่"
                                required></textarea>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-12">
                        <button type="submit"
                            name="register"
                            id="register"
                            value="reg_ok"
                            class="btn-auth btn-primary-custom">
                            <i class="fas fa-user-plus me-2"></i>สมัครสมาชิก
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="auth-footer">
                        มีบัญชีอยู่แล้ว? <a href="login.php" class="auth-link">เข้าสู่ระบบ</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Image Preview
        const avatarPreview = document.getElementById('avatarPreview');
        document.getElementById('user_img').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Password Strength
        document.getElementById('user_pwd').addEventListener('input', function() {
            const strengthBar = document.querySelector('.password-strength-bar');
            const strength = Math.min(this.value.length * 10, 100);
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor =
                strength >= 70 ? 'var(--success)' :
                strength >= 40 ? '#f59e0b' : 'var(--error)';
        });
    </script>
    <!-- เรียกใช้ JavaScript ของ Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>