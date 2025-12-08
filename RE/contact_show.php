<?php
require_once('connection.php');

// ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'role_admin') {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$sql_location = "SELECT * FROM locat ORDER BY CONVERT(loca_name USING tis620) ASC";
$result_location = mysqli_query($project_connect, $sql_location) or die(mysqli_error($project_connect));

$sql_contact = "SELECT * FROM contact ORDER BY CONVERT(cont_name USING tis620) ASC";
$result_contact = mysqli_query($project_connect, $sql_contact) or die(mysqli_error($project_connect));
?>

<!DOCTYPE html>
<html>
<head>
    <title>แสดงข้อมูลการติดต่อ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>ข้อมูลการติดต่อ</h2>
        <a href="contact_add.php" class="btn btn-success mb-3">เพิ่มข้อมูล</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th>ไอคอน</th>
                    <th>ลิงก์</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_result = $result_contact->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row_result['cont_name']); ?></td>
                    <td><?php echo htmlspecialchars($row_result['cont_icon']); ?></td>
                    <td><?php echo htmlspecialchars($row_result['cont_link']); ?></td>
                    <td><a href="contact_edit.php?cont_id=<?php echo $row_result['cont_id']; ?>" class="btn btn-warning">แก้ไข</a></td>
                    <td><a type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal" data-contid="<?php echo $row_result['cont_id']; ?>" data-contname="<?php echo $row_result['cont_name']; ?>">ลบ</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>ข้อมูลสถานที่</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th>ลิงก์</th>
                    <th>แผนที่</th>
                    <th>แก้ไข</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_result1 = $result_location->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row_result1['loca_name']); ?></td>
                    <td><?php echo htmlspecialchars($row_result1 ['loca_link']); ?></td>
                    <td><?php echo htmlspecialchars($row_result1['loca_map']); ?></td>
                    <td><a href="location_edit.php?loca_id=<?php echo $row_result1['loca_id']; ?>" class="btn btn-warning">แก้ไข</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal สำหรับลบ -->
    <div id="deleteModal" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <h4 class="modal-title w-100">ต้องการลบข้อมูลนี้หรือไม่?</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a id="confirmDelete" href="#" class="btn btn-danger">ลบ</a>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var contId = button.data('contid');
        var contName = button.data('contname');
        var modal = $(this);
        modal.find('#deleteMessage').text('คุณต้องการลบข้อมูลการติดต่อ (' + contName + ') นี้หรือไม่?');
        modal.find('#confirmDelete').attr('href', 'con_del.php?cont_id=' + contId);
    });
    </script>

<?php
mysqli_close($project_connect);
?>