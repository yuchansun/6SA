


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Contact - Moderna Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Poppins&family=Raleway&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="contact-page">
<?php include('header.php'); ?>
<style>
  body.contact-page #header {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 999;
  background: rgba(0, 55, 67, 0.95);
}
  </style>
  


  <main class="main">
  <section class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="card shadow p-4">
            <h4 class="text-center mb-4">註冊</h4>
            <form action="signup_process.php" method="post">
            <div class="mb-3">
                <input type="text" name="nickname" class="form-control" placeholder="匿名" required>
              </div>
              <div class="mb-3">
                <input type="email" name="account" class="form-control" placeholder="E-Mail" required>
              </div>
              <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="密碼" required>
              </div>
              <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="確認密碼" required>
              </div>
              <div class="mb-3">
                你是 ： 
              <select name="roles" required>
        <option value="Student">學生</option>
        <option value="Teacher">教師</option>
        </select>
              </div>
              <div class="d-grid">
                <input type="submit" value="註冊" class="btn btn-primary">
              </div>
              <div class="text-center">
</br>
                <small>已經有帳號了？<a href="contact.php"> 登入</a></small>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

</body>
</html>

<!-- Footer -->
<?php include('footer.php'); ?>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>