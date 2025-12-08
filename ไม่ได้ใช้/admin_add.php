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

$sql_script = "SELECT * FROM user ORDER BY CONVERT(user_name USING tis620) ASC";
$result_script = mysqli_query($project_connect, $sql_script) or die(mysqli_connect_error());
$row_script = mysqli_fetch_assoc($result_script);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    header("Content-Type: application/json; charset=utf-8");

    $sql_role = "INSERT INTO role_admin (user_id, admin_show) VALUES (?, ?)";
    $stmt = mysqli_prepare($project_connect, $sql_role);
    mysqli_stmt_bind_param($stmt, "ii", $_POST['user_id'], $_POST['admin_show']);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($project_connect)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($project_connect);
    exit();
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายชื่อสมาชิก Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-4">
        <h2 class="text-center">เพิ่มรายชื่อสมาชิก Admin</h2>
        <form id="user-form" method="POST">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="0">--กรุณาเลือก--</option>
                    <?php do { ?>
                        <option value="<?php echo $row_script['user_id']; ?>"><?php echo $row_script['user_name']; ?></option>
                    <?php } while ($row_script = mysqli_fetch_assoc($result_script)); ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">แสดงผล (1=แสดง, 0=ซ่อน)</label>
                <select name="admin_show" id="admin_show" class="form-control">
                    <option value="">--กรุณาเลือก--</option>
                    <option value="1">แสดง</option>
                    <option value="0">ซ่อน</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success" name="cmd_ok" value="ตกลง">บันทึก</button>
            <a href="user_role.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $("#user_id").change(function() {
                var userId = $(this).val();
                if (userId !== "") {
                    $.ajax({
                        url: "check_admin.php",
                        method: "POST",
                        data: {
                            user_id: userId
                        },
                        success: function(response) {
                            if (response === "exists") {
                                Swal.fire({
                                    icon: "warning",
                                    title: "ผู้ใช้นี้มีตำแหน่งอยู่แล้ว!",
                                    text: "กรุณาเลือกผู้ใช้ใหม่",
                                });
                                $("button[type=submit]").prop("disabled", true);
                            } else {
                                $("button[type=submit]").prop("disabled", false);
                            }
                        }
                    });
                }
            });

            $("#user-form").submit(function(e) {
                e.preventDefault();
                var userId = $("#user_id").val();
                var adminShow = $("#admin_show").val();

                if (userId === "" || adminShow === "") {
                    Swal.fire({
                        icon: "error",
                        title: "กรุณากรอกข้อมูลให้ครบถ้วน",
                    });
                    return;
                }

                $.ajax({
                    method: "POST",
                    dataType: "json", 
                    data: {
                        user_id: userId,
                        admin_show: adminShow
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "เพิ่มตำแหน่งสำเร็จ!",
                                showConfirmButton: false,
                                timer: 1500,
                            }).then(() => {
                                window.location.href = "user_role.php";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "เกิดข้อผิดพลาด!",
                                text: response.message || "ไม่สามารถเพิ่มตำแหน่งได้",
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: "error",
                            title: "เกิดข้อผิดพลาด!",
                            text: "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้",
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>