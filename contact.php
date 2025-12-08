<?php
require_once('connection.php');
include('nav.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อ - พระนครศรีอยุธยา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #ff6f00;
            --accent-color: #4caf50;
            --light-bg: #f8f9fa;
            --dark-text: #212121;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .d-flex.flex-nowrap {
            flex-wrap: nowrap !important;
        }

        .welcome-message,
        .btn-admin,
        .btn-logout,
        .btn-login {
            white-space: nowrap;
        }

        .navbar-nav {
            flex-wrap: nowrap;
        }

        .nav-link {
            white-space: nowrap;
        }

        .nav-item {
            margin-right: 10px;
        }

        .nav-link i {
            font-size: 0.9rem;
        }

        .nav-link {
            font-size: 0.9rem;
            padding: 5px 10px;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            max-width: 100%;
            width: 100%;
            overflow: hidden;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.5rem;

        }

        .navbar .container {
            max-width: 100% !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .welcome-message {
            background-color: #e8f5e9;
            padding: 8px 15px;
            border-radius: 50px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            color: #2e7d32;
        }

        .btn-admin {
            background-color: var(--primary-color);
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s;
        }

        .btn-admin:hover {
            background-color: #0d1b69;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-logout,
        .btn-login {
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s;
        }

        .btn-logout:hover,
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .carousel {
            margin-top: 5px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .carousel-item {
            height: 500px;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100%;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            padding: 20px;
            max-width: 80%;
            margin: 0 auto;
            bottom: 40px;
        }

        .carousel-indicators {
            margin-bottom: 1.5rem;
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            margin: 0 5px;
        }

        .carousel-indicators .active {
            background-color: white;
        }

        .contact-info {
            margin-top: 30px;
        }

        .contact-info h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .contact-info a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: #1a237e;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
            display: block;
            margin-bottom: 10px;
        }

        .footer-links a:hover {
            color: white;
        }

        .map {
            width: 100%;
            height: 400px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $banner_sql = "SELECT * FROM banner WHERE bnn_show = 1";
            $banner_result = mysqli_query($project_connect, $banner_sql);
            $active = "active"; // ให้แบนเนอร์แรกเป็น active
            while ($banner = mysqli_fetch_assoc($banner_result)) {
            ?>
                <div class="carousel-item <?php echo $active; ?>">
                    <!-- แสดงรูปแบนเนอร์ -->
                    <img src="<?php echo htmlspecialchars($banner['bnn_img']); ?>"
                        class="d-block w-100"
                        alt="<?php echo htmlspecialchars($banner['bnn_name']); ?>">

                    <!-- ข้อมูลเหนือรูปภาพและปุ่มลิงก์ -->
                    <div class="carousel-caption d-none d-md-block">
                        <h3><?php echo htmlspecialchars($banner['bnn_name']); ?></h3>
                        <p><?php echo htmlspecialchars($banner['bnn_desc']); ?></p>
                        <!-- ปุ่มลิงก์ “อ่านเพิ่มเติม” -->
                        <a href="<?php echo htmlspecialchars($banner['bnn_link']); ?>"
                            class="btn btn-outline-light btn-sm mt-2">อ่านเพิ่มเติม</a>
                    </div>
                </div>
            <?php
                // หลังจากแบนเนอร์แรกแล้ว ให้เปลี่ยน $active ให้เป็นสตริงว่าง
                $active = "";
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>

    <!-- Contact Information -->
    <div class="container contact-info">
        <h3>ข้อมูลการติดต่อ</h3>
        <?php
        $contact_sql = "SELECT * FROM contact";
        $contact_result = mysqli_query($project_connect, $contact_sql);
        while ($contact = mysqli_fetch_assoc($contact_result)) {
            echo '<p><strong>' . htmlspecialchars($contact['cont_name']) . ':</strong> <a href="' . htmlspecialchars($contact['cont_link']) . '">' . htmlspecialchars($contact['cont_link']) . '</a></p>';
        }
        ?>
    </div>
    
    <div class="container contact-info">
    <h3>สถานที่ตั้ง</h3>
    <?php
    $locat_sql = "SELECT * FROM locat";
    $locat_result = mysqli_query($project_connect, $locat_sql);
    
    if (mysqli_num_rows($locat_result) > 0) {
        while ($locat = mysqli_fetch_assoc($locat_result)) {
            // แสดงชื่อสถานที่และลิงก์
            echo '<p><strong>' . htmlspecialchars($locat['loca_name']) . ':</strong> 
                  <a href="' . htmlspecialchars($locat['loca_link']) . '" target="_blank">' . 
                  htmlspecialchars($locat['loca_link']) . '</a></p>';

            // แปลง HTML entities และตรวจสอบข้อมูลแผนที่
            $mapEmbed = isset($locat['loca_map']) ? htmlspecialchars_decode($locat['loca_map']) : '';

            // ตรวจสอบว่า $mapEmbed มีค่าและเป็น iframe ที่ถูกต้อง
            if (!empty($mapEmbed) && strpos($mapEmbed, '<iframe') !== false) {
                // ทำความสะอาด iframe และเพิ่มคุณสมบัติที่จำเป็น
                $mapEmbed = preg_replace('/width=["\']?\d+["\']?/', 'width="100%"', $mapEmbed);
                $mapEmbed = preg_replace('/height=["\']?\d+["\']?/', 'height="400"', $mapEmbed);
                
                // ถ้าไม่มี frameborder ให้เพิ่มเข้าไป
                if (strpos($mapEmbed, 'frameborder') === false) {
                    $mapEmbed = str_replace('<iframe', '<iframe frameborder="0"', $mapEmbed);
                }
                
                echo '<div class="map">' . $mapEmbed . '</div>';
            } else {
                echo '<p class="text-danger">ไม่พบข้อมูลแผนที่ที่ถูกต้องสำหรับ ' . 
                     htmlspecialchars($locat['loca_name']) . '</p>';
            }
        }
    } else {
        echo '<p class="text-danger">ไม่พบข้อมูลสถานที่ตั้งในฐานข้อมูล</p>';
    }
    ?>
</div>
<?php include('footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>