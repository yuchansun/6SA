<?php
session_start();
$error = '';
$success_message = '';

// Handle form submission for updating information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['roles'];
    $email = $_SESSION['user'];
    $photo = $_FILES['photo'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'sa-6');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch latest role from database to sync session
    $result = $conn->query("SELECT Roles FROM account WHERE `E-mail` = '$email'");
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['role'] = $row['Roles'];
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
                $_SESSION['nickname'] = $nickname;
                $success_message .= "暱稱更新成功! ";
            } else {
                $error .= "暱稱更新失敗，請再試一次。";
            }
            $stmt->close();
        }

        // Handle password update (no hashing)
        if (!empty($new_password)) {
            // Save password directly (plain text)
            $stmt = $conn->prepare("UPDATE account SET Password = ? WHERE `E-mail` = ?");
            $stmt->bind_param("ss", $new_password, $email);
            if ($stmt->execute()) {
                $success_message .= "密碼更新成功! ";
            } else {
                $error .= "密碼更新失敗，請再試一次。";
            }
            $stmt->close();
        }

        // Handle role update
        if (!empty($role)) {
            $stmt = $conn->prepare("UPDATE account SET Roles = ? WHERE `E-mail` = ?");
            $stmt->bind_param("ss", $role, $email);
            if ($stmt->execute()) {
                $_SESSION['role'] = $role;
                $success_message .= "身分更新成功! ";
            } else {
                $error .= "身分更新失敗，請再試一次。";
            }
            $stmt->close();
        }

        // Handle photo upload with validation
        if ($photo['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($photo['type'], $allowed_types)) {
                $error .= "只允許上傳 JPG, PNG 或 GIF 格式的圖片。";
            } else {
                if ($photo['size'] > 5 * 1024 * 1024) {
                    $error .= "圖片檔案大小不能超過 5MB。";
                } else {
                    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $ext;
                    $target_dir = "assets/img/personal_photo/";
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($photo["tmp_name"], $target_file)) {
                        $stmt = $conn->prepare("UPDATE account SET Photo = ? WHERE `E-mail` = ?");
                        $stmt->bind_param("ss", $target_file, $email);
                        if ($stmt->execute()) {
                            $_SESSION['photo'] = $target_file;
                            $success_message .= "照片更新成功! ";
                        } else {
                            $error .= "照片更新失敗，請再試一次。";
                        }
                        $stmt->close();
                    } else {
                        $error .= "照片上傳失敗，請再試一次。";
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
    
    top: 0;
    width: 100%;
    z-index: 999;
    background: rgba(0, 55, 67, 0.95);
  }
  .container {
    
    padding: 35px; /* Add padding to the container */
    
  }
  .left-form {
    
    margin-top:5%;/* Light background for the form */
    display: flex;
    justify-content: flex-start;
    margin-left: 38%; /* Move it more to the left */
  }
  .card {
    max-width: 500px;
     /* Remove the border around the card */
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
                <input type="password" name="new_password" class="form-control" placeholder="更新密碼">
              </div>

              <!-- Confirm New Password -->
              <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="確認更新密碼">
              </div>

              <div class="mb-3">
                        你是 ：
                        <select name="roles" class="form-select" required>
    <option value="學生" <?= isset($_POST['roles']) && $_POST['roles'] == '學生' ? 'selected' : '' ?>>學生</option>
    <option value="學長姐" <?= isset($_POST['roles']) && $_POST['roles'] == '學長姐' ? 'selected' : '' ?>>學長姐</option>
    <option value="教師" <?= isset($_POST['roles']) && $_POST['roles'] == '教師' ? 'selected' : '' ?>>教師</option>
    
</select>
              </br>

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
                <input type="submit" value="保存" class="btn btn-primary">
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
<script>
  // Photo preview function
  function previewPhoto(event) {
    const photoPreview = document.getElementById('photo-preview');
    photoPreview.src = URL.createObjectURL(event.target.files[0]);
    photoPreview.style.display = 'block';
  }
</script>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<!-- Main JS File -->
<script src="assets/js/main.js"></script>

</body>

</html>
