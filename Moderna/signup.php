<?php
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $account = $_POST['account'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $roles = $_POST['roles'];

    if ($password !== $confirm_password) {
        $message = "密碼不一致，請重新輸入。";
    } else {
        $conn = new mysqli('localhost', 'root', '', 'sa-6');
        if ($conn->connect_error) {
            die("資料庫連線失敗：" . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT * FROM account WHERE `E-mail` = ?");
        $stmt->bind_param("s", $account);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "此信箱已被註冊，請使用其他信箱。";
        } else {
            $stmt = $conn->prepare("INSERT INTO account (`E-mail`, `Password`, `Nickname`, `Roles`) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $account, $password, $nickname, $roles);
            if ($stmt->execute()) {
                $_SESSION['user'] = $account;
                $message = "註冊成功！歡迎 " . htmlspecialchars($nickname) . "！<a href='contact.php'>請前往登入畫面</a>";
            } else {
                $message = "註冊失敗，請稍後再試。";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>


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
  <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card p-4 shadow">
                <h4 class="text-center mb-3">註冊</h4>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <input type="text" name="nickname" class="form-control" placeholder="匿稱" required>
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
                        <select name="roles" class="form-select" required>
    <option value="學生" <?= isset($_POST['roles']) && $_POST['roles'] == '學生' ? 'selected' : '' ?>>學生</option>
    <option value="教師" <?= isset($_POST['roles']) && $_POST['roles'] == '教師' ? 'selected' : '' ?>>教師</option>
</select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">註冊</button>
                    </div>
                    <div class="text-center mt-2">
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