<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require_once 'db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = ($currentPage === 'index.php');

$photoPath = 'assets/img/personal_photo/default.jpeg';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT Photo, Roles FROM account WHERE User_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['Photo']) && $row['Photo'] !== 'assets/img/personal_photo/default.jpeg') {
                $photoPath = $row['Photo'];
            }
            $userRole = $row['Roles'];
        }
    }
    $stmt->close();
}

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* 通知側欄樣式 */
    .notification-sidebar {
      position: fixed;
      top: 0;
      right: -320px;
      width: 300px;
      height: 100vh;
      background-color: #fff;
      box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
      padding: 20px;
      transition: right 0.3s ease;
      z-index: 9999;
      overflow-y: auto;
    }
    .notification-sidebar.open {
      right: 0;
    }
    .notification-item {
      border-bottom: 1px solid #ccc;
      padding: 10px 0;
    }
    .notification-item.unread {
      font-weight: bold;
      background-color: #f5f5f5;
    }
  </style>
</head>
<body>

<header id="header" class="header d-flex align-items-center fixed-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

    <a href="index.php" class="logo d-flex align-items-center">
      <h1 class="sitename">特殊選才</h1>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <!-- 未登入時顯示基本選項 -->
        <?php if (!isset($_SESSION['nickname'])): ?> 
          <li><a href="index.php">首頁</a></li>
          <li><a href="about.php">校系簡章</a></li>
          <li><a href="favorite.php">我的最愛</a></li>
          <li><a href="blog-details.php">討論區</a></li>
          <li class="dropdown">
            <a href="#"><span>學群及學校介紹</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="services.php">學群介紹</a></li>
              <li><a href="portfolio.php">學校簡介</a></li>
            </ul>
          </li>
          <li><a href="contact.php">登入</a></li>
        <?php endif; ?>

        <!-- 登入後顯示用戶相關選項 -->
        <?php if (isset($_SESSION['nickname'])): ?> 
          <?php if ($userRole !== '管理者'): ?>
            <!-- 普通使用者看到的選項 -->
            <li><a href="index.php">首頁</a></li>
            <li><a href="about.php">校系簡章</a></li>
            <li><a href="favorite.php">我的最愛</a></li>
            <li><a href="blog-details.php">討論區</a></li>
            <li class="dropdown">
              <a href="#"><span>學群及學校介紹</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="services.php">學群介紹</a></li>
                <li><a href="portfolio.php">學校簡介</a></li>
              </ul>
            </li>
            <li>
              <a href="javascript:void(0);" id="openNotifications">
                <i class="bi bi-bell-fill"></i>
              </a>
            </li> 
            <li class="d-flex align-items-center"><a href="update_personal.php">
              <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 6px;">
              <span>Hi, <?= htmlspecialchars($_SESSION['nickname']) ?></span></a>
            </li>
            <!-- 登出選項 -->
            <li><a href="logout.php">登出</a></li>
          <?php endif; ?>

          <!-- 管理者特有選項 -->
          <?php if ($userRole === '管理者'): ?>
            <li><a href="">管理校系簡章</a></li>
            <li><a href="blog-details.php">管理討論區</a></li>
            <li><a href="teacher_verify.php">教師驗證</a></li>
            <li><a href="">新增管理者</a></li>
            <li class="d-flex align-items-center"><a href="update_personal.php">
              <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 6px;">
              <span>Hi, <?= htmlspecialchars($_SESSION['nickname']) ?></span></a>
            </li>
            <li><a href="logout.php">登出</a></li>
          <?php endif; ?>
        <?php endif; ?>

      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

  </div>
</header>


<!-- 通知側欄 -->
<div id="notificationSidebar" class="notification-sidebar">
  <div>
    <h5>提醒事項</h5>
  </div>
  <ul id="notificationList"></ul>
</div>

<style>
  #notificationList {
  margin: 0;
  padding: 0;
  list-style-type: none; /* 取消無序列表的圓點或標記 */
}

.notification-sidebar {
position: fixed;
  top: 80px; /* 根據 header 高度調整 */
  right: -350px;
  width: 300px;
  height: calc(100% - 150px);
  background: #fff;
 
  box-shadow: -2px 0 5px rgba(0,0,0,0.1);
  transition: right 0.3s ease;
  overflow-y: auto;
  border-radius: 10px;
}

.notification-row {
  display: flex;
  padding: 8px 12px;
  border-bottom: 1px solid #ddd;
  font-size: 14px;
  text-align: left;
  cursor: pointer;
  background-color: #fff;
  margin: 0!important;
}

.notification-row:hover {
  background-color: #f5f5f5;
}

.notification-text {
  flex: 1;
  color: #333;
  margin: 0!important;
}


</style>
<!-- 其他 HTML 內容 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const bell = document.getElementById('openNotifications');
  const sidebar = document.getElementById('notificationSidebar');

  if (bell && sidebar) {
    bell.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // 日期格式化函數
  function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // 跳轉到我的最愛對應項目
  function redirectToFavorite(todoId) {
    window.location.href = `/6SA/Moderna/favorite.php#todo-${todoId}`;

  }

  // 載入通知
  fetch('get_notifications.php')
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('notificationList');
      list.innerHTML = ''; // 清空通知列表

      if (data.length === 0) {
        list.innerHTML = '<div class="notification-empty">目前沒有提醒事項。</div>';
      } else {
        data.forEach(n => {
          const item = document.createElement('div');
item.className = 'notification-row'; // 用新的 class 取代 .notification-card
item.id = `notify-${n.id}`;
item.innerHTML = `
  <div class="notification-text">
    <strong>${n.School_Name} ${n.Department}</strong>：${n.title} 快到期囉！（${formatDate(n.end_time)} 截止）
  </div>
`;


          item.style.cursor = 'pointer'; // 滑鼠指標顯示為可點擊
          item.onclick = () => redirectToFavorite(n.id);

          list.appendChild(item);
        });
      }
    })
    .catch(error => console.error('載入通知時發生錯誤：', error));
});

</script>
</body>
</html>
