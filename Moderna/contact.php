<?php
session_start();
$error = '';
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_to'] = $_GET['redirect'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST['account'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'sa-6');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 修改查詢以獲取用戶資料和教師資訊（如果有）
    $stmt = $conn->prepare("SELECT a.*, t.school_name, t.department, t.employment_status, t.verified 
                           FROM account a 
                           LEFT JOIN teacher_info t ON a.User_ID = t.account_id 
                           WHERE a.`E-mail` = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Plain text password comparison
if ($password === $user['Password']) {
    // Login success
    $_SESSION['user'] = $user['E-mail'];
    $_SESSION['nickname'] = $user['Nickname'];
    $_SESSION['user_id'] = $user['User_ID'];
    $_SESSION['role'] = $user['Roles']; // 儲存用戶角色

    // 如果是教師，存儲教師相關資訊
    if ($user['Roles'] === '教師') {
        $_SESSION['is_teacher'] = true;
        $_SESSION['school_name'] = $user['school_name'] ?? '';
        $_SESSION['department'] = $user['department'] ?? '';
        $_SESSION['employment_status'] = $user['employment_status'] ?? '';
        $_SESSION['teacher_verified'] = $user['verified'] ?? 0;
    }

    // Remember me 功能
    if (isset($_POST['remember'])) {
        setcookie('remember_email', $account, time() + (86400 * 30), "/"); // 30 天
        setcookie('remember_password', $password, time() + (86400 * 30), "/");
    } else {
        setcookie('remember_email', '', time() - 3600, "/");
        setcookie('remember_password', '', time() - 3600, "/");
    }

    // 導向頁面邏輯：依角色導向不同頁面
    if ($_SESSION['role'] === '管理者') {
        header("Location: about.php?admin=1"); // 管理者導向後台首頁
    } else {
        $redirect = $_SESSION['redirect_to'] ?? 'index.php'; // 其他人導向原本頁面或首頁
        unset($_SESSION['redirect_to']);
        header("Location: $redirect");
    }
    exit();
        } else {
            $error = "密碼錯誤.";
        }
    } else {
        $error = "E-mail 錯誤.";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>登入 - 特殊選材網站</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
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
            <h4 class="text-center mb-4">登入</h4>
            <form action="" method="post">
            <?php if (!empty($error)): ?>
  <div class="alert alert-danger text-center">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>
              <div class="mb-3">
              <input type="text" name="account" class="form-control" placeholder="E-Mail" required
              value="<?= isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : '' ?>">
              </div>
              <div class="mb-3">
              <input type="password" name="password" class="form-control" placeholder="密碼" required
              value="<?= isset($_COOKIE['remember_password']) ? htmlspecialchars($_COOKIE['remember_password']) : '' ?>">
              </div>
              <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe" name="remember"
              <?= isset($_COOKIE['remember_email']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="rememberMe">記住我</label>
              </div>
              <div class="d-grid mb-3">
                <input type="submit" value="登入" class="btn btn-primary">
              </div>
              <div class="text-center">
                <small>沒有帳號？<a href="signup.php"> 立即註冊</a></small>
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