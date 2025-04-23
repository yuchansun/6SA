<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = ($currentPage === 'index.php');
$photoPath = isset($_SESSION['photo']) && !empty($_SESSION['photo']) 
    ? $_SESSION['photo']  // This will use the updated photo from session
    : 'assets/img/personal_photo/default.jpeg';


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>特殊選才</title>
  <link rel="icon" href="assets\img\friend.png" type="image/png">
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
  <img src="<?= $photoPath ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 6px;">
  <span>Hi, <?= htmlspecialchars($_SESSION['nickname']) ?></span><li>個人資料</li></a>
</li>

<li><a href="logout.php">登出</a></li>
  <?php else: ?>
    <li><a href="contact.php">登入</a></li>
  <?php endif; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

  </div>
</header>
