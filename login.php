<?php
require_once('connection.php');

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username_value = ""; // ตัวแปรเก็บค่า username ที่ใส่ล่าสุด

if (isset($_POST["login_ok"]) && $_POST["login_ok"] == "login_ok") {
    header("Content-Type:text/html;charset=utf-8");

    $user_name = mysqli_real_escape_string($project_connect, $_POST['user_name']);
    $user_pwd = mysqli_real_escape_string($project_connect, $_POST['user_pwd']);
    
    $username_value = $user_name; // เก็บค่า username เพื่อให้แสดงในฟอร์มอีกครั้ง

    // ค้นหาผู้ใช้ในฐานข้อมูลด้วย Prepared Statements
    $sql_script = "SELECT * FROM user WHERE user_name = ?";
    $stmt = mysqli_prepare($project_connect, $sql_script);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row_result = mysqli_fetch_assoc($result);
    $totalrows_result = mysqli_num_rows($result);

    if ($totalrows_result == 0) {
        // ไม่พบผู้ใช้
        echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');</script>";
    } else {
        // ตรวจสอบสถานะ user_show
        if ($row_result['user_show'] != '1') {
            echo "<script>alert('รหัสถูกระงับ');</script>";
        } elseif (!password_verify($user_pwd, $row_result['user_pwd'])) {
            // รหัสผ่านไม่ถูกต้อง
            echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');</script>";
        } else {
            // เข้าสู่ระบบสำเร็จ
            $_SESSION['user_id'] = $row_result['user_id'];
            $_SESSION['fname'] = $row_result['user_fname'];
            $_SESSION['lname'] = $row_result['user_lname'];

            // ตรวจสอบ role
            $user_id = $row_result['user_id'];
            $role = "user"; // ค่าเริ่มต้นเป็น user ทั่วไป

            // ตรวจสอบว่าเป็น admin
            $admin_query = "SELECT * FROM role_admin WHERE user_id = ? AND admin_show = '1'";
            $admin_stmt = mysqli_prepare($project_connect, $admin_query);
            mysqli_stmt_bind_param($admin_stmt, "i", $user_id);
            mysqli_stmt_execute($admin_stmt);
            $admin_result = mysqli_stmt_get_result($admin_stmt);
            if (mysqli_num_rows($admin_result) > 0) {
                $role = "role_admin";
            }
            mysqli_stmt_close($admin_stmt);

            // ตรวจสอบว่าเป็น supervisor
            $sup_query = "SELECT * FROM role_sup WHERE user_id = ? AND sup_show = '1'";
            $sup_stmt = mysqli_prepare($project_connect, $sup_query);
            mysqli_stmt_bind_param($sup_stmt, "i", $user_id);
            mysqli_stmt_execute($sup_stmt);
            $sup_result = mysqli_stmt_get_result($sup_stmt);
            if (mysqli_num_rows($sup_result) > 0) {
                $role = "role_sup";
            }
            mysqli_stmt_close($sup_stmt);

            $_SESSION['role'] = $role; // เก็บค่า role ไว้ใน session
            header("Location: index.php");
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($project_connect);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            position: relative; /* เพิ่มเพื่อให้ปุ่มย้อนกลับอยู่ใน wrapper */
        }

        .auth-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 2.5rem;
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

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
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

        /* ปรับปุ่มย้อนกลับให้อยู่บนซ้าย */
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
            .auth-wrapper {
                padding: 1rem;
            }
            
            .auth-card {
                padding: 1.5rem;
            }

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

<div class="auth-wrapper">
    <!-- ปุ่มย้อนกลับย้ายมาอยู่บนซ้าย -->
    <a href="index.php" class="btn btn-outline-primary back-btn">
        <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
    </a>
    
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title"><i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</h1>
            <p class="auth-subtitle">กรุณากรอกข้อมูลเพื่อใช้งานระบบ</p>
        </div>

        <form method="POST">
            <!-- Username -->
            <div class="input-group-custom">
                <i class="fas fa-user input-icon"></i>
                <input type="text" 
                       class="form-control-custom" 
                       id="user_name" 
                       name="user_name" 
                       placeholder="ชื่อผู้ใช้"
                       value="<?php echo htmlspecialchars($username_value); ?>"
                       required>
            </div>

            <!-- Password -->
            <div class="input-group-custom">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" 
                       class="form-control-custom" 
                       id="user_pwd" 
                       name="user_pwd" 
                       placeholder="รหัสผ่าน"
                       required>
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    name="login_ok" 
                    id="login_ok" 
                    value="login_ok" 
                    class="btn-auth btn-primary-custom mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
            </button>

            <!-- Register Link -->
            <div class="auth-footer">
                ยังไม่มีบัญชี? <a href="register.php" class="auth-link">สมัครสมาชิก</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#user_pwd');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>