<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>footer {
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
}</style>
</head>
<body>
    <!-- footer.php -->
<footer>
  <div class="container">
    <div class="row">
      <!-- ส่วนข้อมูลเกี่ยวกับเว็บ -->
      <div class="col-md-4 mb-4">
        <h5>เกี่ยวกับเรา</h5>
        <p>เว็บไซต์นี้จัดทำขึ้นเพื่อส่งเสริมการท่องเที่ยวและวัฒนธรรมของพระนครศรีอยุธยา มรดกโลกทางวัฒนธรรม</p>
      </div>

      <!-- ส่วนลิงก์ด่วน -->
      <div class="col-md-4 mb-4">
        <h5 class="footer-links">ลิงก์ด่วน</h5>
        <div class="footer-links">
          <a href="index.php">หน้าหลัก</a>
          <a href="attraction.php">สถานที่ท่องเที่ยว</a>
          <a href="tradition.php">ประเพณี</a>
          <a href="product.php">สินค้าแนะนำ</a>
          <a href="contact.php">ติดต่อเรา</a>
        </div>
      </div>

      <!-- ส่วนโซเชียลมีเดียและข้อมูลติดต่อ -->
      <div class="col-md-4 mb-4">
        <h5>ติดตามเรา</h5>
        <!-- <div class="social-links">
          <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
          <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
          <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
          <a href="#" target="_blank"><i class="fab fa-youtube"></i></a>
        </div> -->
        <p class="mt-3">อีเมล: saraban_ayutthaya@moi.go.th<br>โทร: 0-3533-6554 ต่อ 101</p>
      </div>
    </div>

    <!-- ส่วนลิขสิทธิ์ -->
    <div class="text-center mt-4">
      <p>&copy; <?php echo date("Y"); ?> พระนครศรีอยุธยา - มรดกโลก. สงวนลิขสิทธิ์.</p>
    </div>
  </div>
</footer>

    
</body>
</html>