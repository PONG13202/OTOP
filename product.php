<?php
require_once('connection.php');
include('nav.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>สินค้า - พระนครศรีอยุธยา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet" />
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

        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .section-title {
            position: relative;
            margin-bottom: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            display: inline-block;
        }

        .section-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 4px;
            background-color: var(--secondary-color);
            border-radius: 2px;
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

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin-right: 10px;
            color: white;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .carousel-item {
                height: 350px;
            }

            .carousel-caption {
                bottom: 20px;
                padding: 10px;
            }

            .welcome-message {
                font-size: 0.9rem;
                padding: 5px 10px;
            }
        }

        .custom-select {
            padding: 10px 20px;
            margin-bottom: 30px;
            border: none;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            font-weight: 400;
            color: #212121;
            cursor: pointer;
        }

        .custom-select:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .custom-select option {
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 400;
            color: #212121;
            background-color: #f8f9fa;
        }

        .custom-select option:hover {
            background-color: #e8f5e9;
        }
    </style>
</head>

<body>
    <!-- Navbar -->

    <!-- Banner Carousel -->
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

    <!-- Products List -->
    <div class="container my-5">
        <h2 class="section-title">สินค้าแนะนำทั้งหมด</h2>
        <div class="row">
            <div class="col-md-12">
                <h2>เลือกประเภทสินค้า</h2>
                <form method="GET" action="product.php">
                    <select name="product_type" class="form-select custom-select" onchange="this.form.submit(); return false;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $type_sql = "SELECT * FROM product_type WHERE pty_show = 1 ORDER BY pty_name ASC";
                        $type_result = mysqli_query($project_connect, $type_sql);
                        while ($type_row = mysqli_fetch_assoc($type_result)) {
                            $selected = (isset($_GET['product_type']) && $_GET['product_type'] == $type_row['pty_id']) ? 'selected' : '';
                            echo "<option value=\"{$type_row['pty_id']}\" $selected>{$type_row['pty_name']}</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="row">
            <?php
            $product_type_id = isset($_GET['product_type']) ? intval($_GET['product_type']) : 0;
            $product_sql = "SELECT p.prd_id, p.prd_name, p.prd_price, pi.prd_img_name 
                        FROM product p 
                        LEFT JOIN product_img pi 
                        ON p.prd_id = pi.prd_id AND pi.prd_img_show = 1 
                        WHERE p.prd_show = 1";
            if ($product_type_id > 0) {
                $product_sql .= " AND p.pty_id = $product_type_id";
            }
            $product_result = mysqli_query($project_connect, $product_sql);
            while ($product = mysqli_fetch_assoc($product_result)) {
                echo '<div class="col-md-4 mb-4">';
                echo '<div class="card">';
                // ตรวจสอบว่ามีรูปหรือไม่ ถ้าไม่มีให้ใช้ placeholder
                $img_src = $product['prd_img_name'] ? htmlspecialchars($product['prd_img_name']) : 'https://via.placeholder.com/200';
                echo '<img src="' . $img_src . '" class="card-img-top" alt="' . htmlspecialchars($product['prd_name']) . '">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($product['prd_name']) . '</h5>';
                echo '<p class="card-text">ราคา: ' . htmlspecialchars($product['prd_price']) . ' บาท</p>';
                echo '<a href="product_full.php?prd_id=' . $product['prd_id'] . '" class="btn btn-primary">ดูรายละเอียด</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <script>
        document.querySelector('.custom-select').addEventListener('change', function() {
            this.form.submit();
            return false;
        });

        $('.custom-select').on('change', function() {
            $(this).closest('form').submit();
            return false;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>