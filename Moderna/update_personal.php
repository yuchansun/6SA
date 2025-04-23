<?php
session_start();
$error = '';
$success_message = '';

// Handle form submission for updating information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['user'];
    $photo = $_FILES['photo'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'sa-6');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if password fields match
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "密碼不匹配。";
    } else {
        // Handle nickname update
        if (!empty($nickname)) {
            $stmt = $conn->prepare("UPDATE account SET Nickname = ? WHERE `E-mail` = ?");
            $stmt->bind_param("ss", $nickname, $email);
            if ($stmt->execute()) {
                $_SESSION['nickname'] = $nickname; // <- This updates the session used in header
                $success_message = "暱稱更新成功!";
            } else {
                $error = "暱稱更新失敗，請再試一次。";
            }
            $stmt->close();
        }

        // Handle password update
        if (!empty($new_password)) {
            $stmt = $conn->prepare("UPDATE account SET Password = ? WHERE `E-mail` = ?");
            $stmt->bind_param("ss", $new_password, $email);
            if ($stmt->execute()) {
                $success_message = "密碼更新成功!";
            } else {
                $error = "密碼更新失敗，請再試一次。";
            }
            $stmt->close();
        }

        // Handle photo upload with validation
        if ($photo['error'] == 0) {
            // Check if file type is allowed
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($photo['type'], $allowed_types)) {
                $error = "只允許上傳 JPG, PNG 或 GIF 格式的圖片。";
            } else {
                // Check if file size is less than 5MB
                if ($photo['size'] > 5 * 1024 * 1024) {
                    $error = "圖片檔案大小不能超過 5MB。";
                } else {
                    // Set the target directory and file name
                    $target_dir = "assets/img/personal_photo";
                    $target_file = $target_dir . basename($photo["name"]);
                    
                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($photo["tmp_name"], $target_file)) {
                        // Save the photo path to the database
                        $stmt = $conn->prepare("UPDATE account SET Photo = ? WHERE `E-mail` = ?");
                        $stmt->bind_param("ss", $target_file, $email);
                        if ($stmt->execute()) {
                            $success_message = "照片更新成功!";
                        } else {
                            $error = "照片更新失敗，請再試一次。";
                        }
                        $stmt->close();
                    } else {
                        $error = "照片上傳失敗，請再試一次。";
                    }
                }
            }
        }
    }

    $conn->close();
}
?>
<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>更新個人資訊</title>
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
<style>
  body.contact-page #header {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 999;
    background: rgba(0, 55, 67, 0.95);
  }
  .container {
    max-width: 960px;
  }
  .left-form {
    display: flex;
    justify-content: flex-start;
    margin-left: 40%; /* Move it more to the left */
  }
  .card {
    max-width: 500px;
    border: none; /* Remove the border around the card */
  }
  #photo-preview {
    max-width: 100%;
    max-height: 200px;
    margin-top: 15px;
  }
</style>

<main class="main">
  <section class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="container">
      <div class="row justify-content-start">
        <div class="col-md-6 col-lg-4 left-form">
          <div class="card shadow p-4">
            <h4 class="text-center mb-4">更新個人資料</h4>
            <form action="" method="post" enctype="multipart/form-data">
              <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php elseif (!empty($success_message)): ?>
                <div class="alert alert-success text-center">
                  <?= htmlspecialchars($success_message) ?>
                </div>
              <?php endif; ?>

              <!-- Nickname Update -->
              <div class="mb-3">
                <input type="text" name="nickname" class="form-control" placeholder="暱稱" value="<?= $_SESSION['nickname'] ?>" required>
              </div>

              <!-- New Password -->
              <div class="mb-3">
                <input type="password" name="new_password" class="form-control" placeholder="新密碼">
              </div>

              <!-- Confirm New Password -->
              <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="確認新密碼">
              </div>

              <!-- Photo Upload -->
              <div class="mb-3">
                <label for="photo" class="form-label">上傳照片</label>
                <input type="file" name="photo" class="form-control" id="photo" onchange="previewPhoto(event)">
              </div>

              <!-- Photo Preview -->
              <div class="mb-3">
                <img id="photo-preview" src="<?= isset($_SESSION['photo']) ? $_SESSION['photo'] : '' ?>" alt="Your Photo">
              </div>

              <!-- Submit Button -->
              <div class="d-grid mb-3">
                <input type="submit" value="更新資料" class="btn btn-primary">
              </div>

              <!-- Link back to Profile -->
              <div class="text-center">
                <small><a href="index.php">返回首頁</a></small>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

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

<!-- Photo Preview Script -->
<script>
  function previewPhoto(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = function() {
      document.getElementById('photo-preview').src = reader.result;
    };
    reader.readAsDataURL(file);
  }
</script>

</body>

</html>
