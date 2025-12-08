<?php
require_once('connection.php');
include('nav.php');

// รับค่า att_id จากพารามิเตอร์ใน URL
$att_id = isset($_GET['att_id']) ? (int)$_GET['att_id'] : 0;

// ตรวจสอบว่ามี att_id หรือไม่ ถ้าไม่มีจะรีไดเรกต์หรือแสดงข้อความผิดพลาดได้
if ($att_id === 0) {
  header('Location: attraction.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลสถานที่ท่องเที่ยว - พระนครศรีอยุธยา</title>
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

    .gallery-thumbs {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
    }

    .gallery-thumbs img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      cursor: pointer;
      border: 1px solid #ddd;
      border-radius: 5px;
      transition: transform 0.3s;
    }

    .gallery-thumbs img:hover {
      transform: scale(1.05);
    }

    .detail-content p {
      text-align: justify;
      line-height: 1.6;
      font-size: 1.1rem;
    }

    .map-section iframe {
      width: 100%;
      height: 400px;
      border: none;
      border-radius: 10px;
    }

    .btn-primary,
    .btn-outline-primary {
      border-radius: 50px;
      transition: all 0.3s;
    }

    .btn-primary:hover,
    .btn-outline-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .detail-title {
      font-weight: 600;
      color: var(--primary-color);
      font-size: 2rem;
      margin-top: 20px;
    }

    .detail-content {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .map-section iframe {
      width: 100%;
      height: 400px;
      border: none;
      border-radius: 10px;
    }
  </style>
</head>

<body>
  <!-- Navbar (nav.php ถ้ามีเมนูหรือโลโก้ ก็จะถูกรวมที่นี่) -->

  <!-- Banner Carousel -->
  <?php
  // ส่วนของ Banner (ถ้ามี)
  $banner_sql = "SELECT * FROM banner WHERE bnn_show = 1";
  $banner_result = mysqli_query($project_connect, $banner_sql);
  $banner_active = "active";
  ?>
  <?php if (mysqli_num_rows($banner_result) > 0): ?>
    <div id="bannerCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php while ($bn = mysqli_fetch_assoc($banner_result)): ?>
          <div class="carousel-item <?php echo $banner_active; ?>">
            <img
              src="<?php echo htmlspecialchars($bn['bnn_img']); ?>"
              class="d-block w-100"
              alt="<?php echo htmlspecialchars($bn['bnn_name']); ?>">
            <div class="carousel-caption d-none d-md-block">
              <h3><?php echo htmlspecialchars($bn['bnn_name']); ?></h3>
              <p><?php echo htmlspecialchars($bn['bnn_desc']); ?></p>
              <?php if (!empty($bn['bnn_link'])): ?>
                <a
                  href="<?php echo htmlspecialchars($bn['bnn_link']); ?>"
                  class="btn btn-outline-light btn-sm mt-2">
                  อ่านเพิ่มเติม
                </a>
              <?php endif; ?>
            </div>
          </div>
          <?php $banner_active = ""; ?>
        <?php endwhile; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
      </button>
    </div>
  <?php endif; ?>

  <!-- ปุ่มย้อนกลับ -->
  <div class="container">
    <a href="attraction.php" class="btn btn-outline-primary mb-3">
      <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>
  </div>
  <div class="container">
    <?php
    // ดึงข้อมูลสถานที่ที่ต้องการโดยระบุ att_id
    $sql = "
            SELECT a.att_id, a.att_name, a.att_detail, a.att_location, a.att_map,
                   GROUP_CONCAT(ai.att_img_name) AS images
              FROM attraction a
              LEFT JOIN attraction_img ai 
                     ON a.att_id = ai.att_id
             WHERE a.att_show = 1
               AND a.att_id = $att_id
             GROUP BY a.att_id
             LIMIT 1
        ";
    $query = mysqli_query($project_connect, $sql);
    $data = mysqli_fetch_assoc($query);

    if (!$data) {
      echo '<h2 class="section-title my-5">ไม่พบข้อมูลสถานที่</h2>';
    } else {
      $att_name = htmlspecialchars($data['att_name']);
      $att_detail = $data['att_detail'];
      $att_location = htmlspecialchars($data['att_location']);
      $att_map = $data['att_map'];

      $images_array = [];
      if (!empty($data['images'])) {
        $images_array = explode(',', $data['images']);
      }
    ?>

      <h1 class="section-title my-5"><?php echo $att_name; ?></h1>

      <!-- ถ้ามีหลายรูป สร้างคารูเซล หรือโชว์เป็น Thumbnails -->
      <?php if (!empty($images_array)): ?>
        <!-- Carousel หลายรูป -->
        <div id="attractionCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php
            $first_active = "active";
            foreach ($images_array as $img) {
              $img = htmlspecialchars($img);
              if (empty($img)) {
                continue;
              }
            ?>
              <div class="carousel-item <?php echo $first_active; ?>">
                <img
                  src="<?php echo $img; ?>"
                  class="d-block w-100"
                  alt="<?php echo $att_name; ?>">
              </div>
            <?php
              $first_active = "";
            }
            ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#attractionCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#attractionCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        </div>

        <!-- แสดง gallery thumbnails -->
        <div class="gallery-thumbs mb-3">
          <?php foreach ($images_array as $img) {
            $img = htmlspecialchars($img);
            if (empty($img)) {
              continue;
            }
          ?>
            <img
              src="<?php echo $img; ?>"
              alt="thumb"
              onclick="showImageInCarousel(this.src)">
          <?php } ?>
        </div>
      <?php else: ?>
        <!-- ถ้าไม่มีรูปให้แสดงภาพ placeholder -->
        <div class="mb-3">
          <img
            src="https://via.placeholder.com/800x400?text=No+Image"
            class="img-fluid rounded"
            alt="No Image">
        </div>
      <?php endif; ?>

      <!-- รายละเอียดสถานที่ -->
      <div class="detail-content mb-5">
        <h4 class="detail-title">รายละเอียดสถานที่</h4>
        <p><?php echo nl2br(htmlspecialchars($att_detail)); ?></p>
        <h4 class="detail-title">สถานที่ตั้ง:</h4>
        <p><?php echo $att_location; ?></p>
      </div>

      <!-- แสดงแผนที่ถ้ามี -->
      <!-- แสดงแผนที่ถ้ามี -->
      <!-- ... ส่วนอื่น ๆ ก่อนหน้านี้เหมือนเดิม ... -->

      <?php
      $map_html = $att_map; // ค่า iframe จากฐานข้อมูล

      // ตรวจสอบว่ามีค่าในตัวแปร $map_html และเป็น <iframe> ไหม
      if (!empty($map_html) && strpos($map_html, '<iframe') !== false) {
        // ตัวอย่างการปรับความกว้างและสูง
        $map_html = preg_replace('/width=["\']?\d+["\']?/', 'width="100%"', $map_html);
        $map_html = preg_replace('/height=["\']?\d+["\']?/', 'height="400"', $map_html);

        // ถ้าไม่มี frameborder ให้เพิ่มเข้าไป
        if (strpos($map_html, 'frameborder') === false) {
          $map_html = str_replace('<iframe', '<iframe frameborder="0"', $map_html);
        }

        // แสดงโค้ด iframe
        echo '<div class="map-section mb-5">' . $map_html . '</div>';
      } else {
        // ขึ้นข้อความเตือนหรือจะไม่แสดงอะไรเลยก็ได้
        echo '<p class="text-danger">ไม่พบข้อมูลแผนที่ที่ถูกต้อง</p>';
      }
      ?>

      <!-- ... ส่วนอื่น ๆ ต่อจากนี้ ... -->

    <?php
    } // end if data
    ?>
  </div>
  <?php include('footer.php'); ?>

  <script>
    function showImageInCarousel(src) {
      // หาตำแหน่งรูปใน carousel
      const items = document.querySelectorAll('#attractionCarousel .carousel-item');
      let indexToShow = 0;
      items.forEach((item, index) => {
        const imgTag = item.querySelector('img');
        if (imgTag && imgTag.getAttribute('src') === src) {
          indexToShow = index;
        }
      });
      // สั่ง carousel ให้เลื่อนไป
      const carousel = new bootstrap.Carousel('#attractionCarousel');
      carousel.to(indexToShow);
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>