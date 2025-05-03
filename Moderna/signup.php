<?php
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $account = $_POST['account'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $roles = $_POST['roles'];
    
    // 針對教師角色額外收集資訊
    $school_name = isset($_POST['school_name']) ? $_POST['school_name'] : "";
    $department = isset($_POST['department']) ? $_POST['department'] : "";
    $employment_status = isset($_POST['employment_status']) ? $_POST['employment_status'] : "";

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
            // 開始一個事務，確保資料一致性
            $conn->begin_transaction();
            
            try {
                // 插入基本帳號資訊
                $stmt = $conn->prepare("INSERT INTO account (`E-mail`, `Password`, `Nickname`, `Roles`) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $account, $password, $nickname, $roles);
                $stmt->execute();
                
                // 獲取剛剛插入的用戶ID
                $user_id = $conn->insert_id;
                
                // 如果是教師，則加入額外的教師資訊
                if ($roles == "教師") {
                    // 插入教師特定資訊到新表格
                    $stmt_teacher = $conn->prepare("INSERT INTO teacher_info (account_id, school_name, department, employment_status) VALUES (?, ?, ?, ?)");
                    $stmt_teacher->bind_param("isss", $user_id, $school_name, $department, $employment_status);
                    $stmt_teacher->execute();
                }
                
                // 提交事務
                $conn->commit();
                
                $_SESSION['user'] = $account;
                $message = "註冊成功！歡迎 " . htmlspecialchars($nickname) . "！<a href='contact.php'>請前往登入畫面</a>";
            } catch (Exception $e) {
                // 發生錯誤，回滾事務
                $conn->rollback();
                $message = "註冊失敗，請稍後再試。錯誤：" . $e->getMessage();
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>註冊 - 特殊選材網站</title>
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
  
  #teacherFields {
    display: none;
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
                        <select name="roles" id="roles" class="form-select" required onchange="toggleTeacherFields()">
                            <option value="學生" <?= isset($_POST['roles']) && $_POST['roles'] == '學生' ? 'selected' : '' ?>>學生</option>
                            <option value="學長姐" <?= isset($_POST['roles']) && $_POST['roles'] == '學長姐' ? 'selected' : '' ?>>學長姐</option>
                            <option value="教師" <?= isset($_POST['roles']) && $_POST['roles'] == '教師' ? 'selected' : '' ?>>教師</option>
                        </select>
                    </div>
                    
                    <!-- 教師專用欄位 -->
                    <div id="teacherFields">
                        <div class="mb-3">
                            <input type="text" name="school_name" class="form-control" placeholder="任教學校名稱">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="department" class="form-control" placeholder="系所名稱">
                        </div>
                        <div class="mb-3">
                            <select name="employment_status" class="form-select">
                                <option value="">請選擇任職狀態</option>
                                <option value="專任">專任</option>
                                <option value="兼任">兼任</option>
                            </select>
                        </div>
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

<!-- 控制教師欄位顯示/隱藏的 JavaScript -->
<script>
  function toggleTeacherFields() {
    var role = document.getElementById('roles').value;
    var teacherFields = document.getElementById('teacherFields');
    
    if (role === '教師') {
      teacherFields.style.display = 'block';
      // 當選擇教師時，設置額外欄位為必填
      var teacherInputs = teacherFields.querySelectorAll('input, select');
      teacherInputs.forEach(function(input) {
        input.setAttribute('required', 'required');
      });
    } else {
      teacherFields.style.display = 'none';
      // 當不選擇教師時，移除必填屬性
      var teacherInputs = teacherFields.querySelectorAll('input, select');
      teacherInputs.forEach(function(input) {
        input.removeAttribute('required');
      });
    }
  }
  
  // 頁面載入時執行一次，以處理可能的預設值
  document.addEventListener('DOMContentLoaded', function() {
    toggleTeacherFields();
  });
</script>

</body>

</html>