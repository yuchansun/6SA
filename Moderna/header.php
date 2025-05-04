<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require_once 'db.php'; // make sure this points to your DB connection file

$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = ($currentPage === 'index.php');

$photoPath = 'assets/img/personal_photo/default.jpeg'; // Default photo path

// Get photo from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT Photo, Roles FROM account WHERE User_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Check if the user has uploaded a photo or if it's empty
            if (!empty($row['Photo']) && $row['Photo'] !== 'assets/img/personal_photo/default.jpeg') {
                $photoPath = $row['Photo']; // Use user's uploaded photo if it's not the default
            }
            $userRole = $row['Roles']; // Get user role (e.g., '教師')
        }
    }
    $stmt->close();
}

// 點擊討論區檢查
if ($currentPage === 'blog-details.php' && !isset($_SESSION['user'])) {
    $_SESSION['redirect_to'] = 'blog-details.php'; 
    header('Location: contact.php'); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>特殊選才</title>
  <link rel="icon" href="assets/img/friend.png" type="image/png">
  <link rel="stylesheet" href="style.css">
</head>
<body>
</body>
</html>

<header id="header" class="header d-flex align-items-center fixed-top ">
  <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

    <a href="index.php" class="logo d-flex align-items-center">
      <h1 class="sitename">特殊選才</h1>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="index.php">首頁</a></li>
        <li><a href="about.php">校系簡章</a></li>
        <li><a href="favorite.php">我的最愛</a></li>
        <li><a href="blog-details.php">討論區</a></li>
        <li class="dropdown"><a href="#"><span>學群及學校介紹</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
          <ul>
            <li><a href="services.php">學群介紹</a></li>
            <li><a href="portfolio.php">學校簡介</a></li>
          </ul>
        </li>

        <?php if (isset($_SESSION['nickname'])): ?>
          <li class="d-flex align-items-center"><a href="update_personal.php">
            <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 6px;">
            <span>Hi, <?= htmlspecialchars($_SESSION['nickname']) ?></span></a>
          </li>
          <li><a href="logout.php">登出</a></li>
          
          <!-- Add Teacher Verification link for 教師 role -->
          <?php if ($userRole === '管理者'): ?>
            <li><a href="admin.php">管理者操作</a></li>
          <?php endif; ?>
        <?php else: ?>
          <li><a href="contact.php">登入</a></li>
        <?php endif; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

  </div>
</header>
