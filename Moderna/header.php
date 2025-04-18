<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = ($currentPage === 'index.php');
?>

<header id="header" class="header d-flex align-items-center fixed-top <?php echo $isHome ? 'transparent-header' : ''; ?>">
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
          <li>Hi, <?= htmlspecialchars($_SESSION['nickname']) ?></li>
          <li><a href="update_personal.php">個人資料</a></li> <!-- Link to profile page -->
          <li><a href="logout.php">登出</a></li>
        <?php else: ?>
          <li><a href="contact.php">登入</a></li>
        <?php endif; ?>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

  </div>
</header>
