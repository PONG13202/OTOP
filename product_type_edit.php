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

if (isset($_GET['pty_id']) && !empty($_GET['pty_id'])) {
    $pty_id = $_GET['pty_id'];

    $sql_pty = "SELECT * FROM product_type WHERE pty_id = '$pty_id'";
    $result_pty = mysqli_query($project_connect, $sql_pty);
    if (mysqli_num_rows($result_pty) > 0) {
        $row_pty = mysqli_fetch_assoc($result_pty);
    } else {
        echo "ไม่พบแบนเนอร์ที่คุณต้องการแก้ไข";
        exit();
    }
} else {
    echo "ไม่พบรหัสแบนเนอร์";
    exit();
}

if (isset($_POST['submit'])) {
    $pty_name = $_POST['pty_name'];
    $pty_detail = $_POST['pty_detail'];
    $pty_show = $_POST['pty_show'];

    $target_dir = "uploads/product_type/";
    $target_file = $target_dir . basename($_FILES["pty_img"]["name"] ?? $row_pty['pty_img']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (isset($_FILES["pty_img"]) && $_FILES["pty_img"]["error"] == 0) {

        $check = getimagesize($_FILES["pty_img"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.<br>";
            $uploadOk = 0;
        }

        if ($_FILES["pty_img"]["size"] > 5000000) {
            echo "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["pty_img"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["pty_img"]["name"])) . " has been uploaded.<br>";
            } else {
                echo "Sorry, there was an error uploading your file.<br>";
            }
        } else {
            echo "Sorry, your file was not uploaded.<br>";
        }
    } else {

        $target_file = $row_pty['pty_img'];
    }

    $sql_update = "UPDATE product_type SET pty_name = '$pty_name', pty_detail = '$pty_detail', pty_img = '$target_file', pty_show = '$pty_show' WHERE pty_id = '$pty_id'";
    if (mysqli_query($project_connect, $sql_update)) {
        header("Location: product_show.php");
    } else {
        echo "Error updating record: " . mysqli_error($project_connect);
    }
}
?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขประเภทสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</head>

<body>

<div class="main-content">
    <div class="container mt-4  form-container">
        <h3 class="text-center">แก้ไขประเภทสินค้า</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pty_name" class="form-label">ชื่อประเภทสินค้า</label>
                <input type="text" class="form-control" id="pty_name" name="pty_name" value="<?php echo htmlspecialchars($row_pty['pty_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="pty_detail" class="form-label">คำอธิบายประเภทสินค้า</label>
                <input type="text" class="form-control" id="pty_detail" name="pty_detail" value="<?php echo htmlspecialchars($row_pty['pty_detail']); ?>" required>
            </div>

            <div class="current-image">
                <label for="pty_img" class="form-label">รูปภาพปัจจุบัน</label>
                <br>
                <img src="<?php echo htmlspecialchars($row_pty['pty_img']); ?>" alt="Current Banner Image" width="150">
            </div>

            <div class="mb-3">
                <label for="pty_img" class="form-label">อัปโหลดรูปภาพใหม่</label>
                <input type="file" class="form-control" id="pty_img" name="pty_img">
            </div>

            <div class="mb-3">
                <select name="pty_show" class="form-control">
                    <option value="9">--กรุณาเลือก--</option>
                    <option value="1" <?php echo ($row_pty['pty_show'] == 1) ? 'selected' : ''; ?>>แสดง</option>
                    <option value="0" <?php echo ($row_pty['pty_show'] == 0) ? 'selected' : ''; ?>>ซ่อน</option>
                </select>
            </div>

            <div class="mb-4 text-center">
                <button type="submit" name="submit" class="btn btn-primary">อัปเดตแบนเนอร์</button>
                <a href="product_show.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>