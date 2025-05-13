<?php ob_start(); include('header.php'); ?>


<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// 如果使用者未登入，儲存當前頁面到 session 並導向登入頁面


// 連接資料庫
require_once 'db.php';

// 檢查是否執行空搜尋
if (isset($_GET['search']) && trim($_GET['search']) === '') {
  header("Location: blog-details.php");
  exit;
}

// 獲取使用者已點讚的文章與留言
$likedPostIds = [];
$likedCommentIds = [];
if (isset($_SESSION['user'])) {
  $userEmail = $_SESSION['user'];
  $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
  $stmt->bind_param("s", $userEmail);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['User_ID'];

    // 獲取已點讚的文章
    $likedPostsQuery = $conn->prepare("SELECT Post_ID FROM likes WHERE User_ID = ? AND Post_ID IS NOT NULL");
    $likedPostsQuery->bind_param("i", $userId);
    $likedPostsQuery->execute();
    $likedPostsResult = $likedPostsQuery->get_result();
    while ($row = $likedPostsResult->fetch_assoc()) {
      $likedPostIds[] = $row['Post_ID'];
    }

    // 獲取已點讚的留言
    $likedCommentsQuery = $conn->prepare("SELECT Comment_ID FROM likes WHERE User_ID = ? AND Comment_ID IS NOT NULL");
    $likedCommentsQuery->bind_param("i", $userId);
    $likedCommentsQuery->execute();
    $likedCommentsResult = $likedCommentsQuery->get_result();
    while ($row = $likedCommentsResult->fetch_assoc()) {
      $likedCommentIds[] = $row['Comment_ID'];
    }
  }
  $stmt->close();
}

// 將點讚資料傳遞給前端
echo "<script>
    const likedPostIds = " . json_encode($likedPostIds) . ";
    const likedCommentIds = " . json_encode($likedCommentIds) . ";
</script>";

// 處理新增貼文提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['comment'])) {
  $title = $conn->real_escape_string(trim($_POST['title']));
  $content = $conn->real_escape_string(trim(str_replace(["\r", "\n"], "", $_POST['comment']))); // 移除 \r 和 \n

  // 從 SESSION 中取得使用者的 E-mail
  $userEmail = $_SESSION['user'];

  // 查詢 account 表以獲取 User_ID
  $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
  $stmt->bind_param("s", $userEmail);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['User_ID'];

    // 插入貼文
    $insertPost = $conn->prepare("INSERT INTO posts (Title, Content, User_ID, Post_Time) VALUES (?, ?, ?, NOW())");
    $insertPost->bind_param("ssi", $title, $content, $userId);
    $insertPost->execute();
    header("Location: blog-details.php");
    exit;
  } else {
    echo "無法找到對應的使用者資訊。";
  }

  $stmt->close();
}
// 處理刪除貼文
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
  $postId = intval($_POST['delete_post_id']);
  $userEmail = $_SESSION['user'] ?? null;

  if ($userEmail) {
      $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
      $stmt->bind_param("s", $userEmail);
      $stmt->execute();
      $userResult = $stmt->get_result();
      if ($userResult->num_rows > 0) {
          $user = $userResult->fetch_assoc();
          $userId = $user['User_ID'];

          // 確保是自己的貼文才能刪
          $checkPost = $conn->prepare("SELECT * FROM posts WHERE Post_ID = ? AND User_ID = ?");
          $checkPost->bind_param("ii", $postId, $userId);
          $checkPost->execute();
          $postResult = $checkPost->get_result();
          if ($postResult->num_rows > 0) {
              $deletePost = $conn->prepare("UPDATE posts SET is_deleted = 1 WHERE Post_ID = ?");
              $deletePost->bind_param("i", $postId);
              $deletePost->execute();
          }
      }
  }
  header("Location: blog-details.php");
  exit;
}

// 處理刪除留言
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
  $commentId = intval($_POST['delete_comment_id']);
  $userEmail = $_SESSION['user'] ?? null;

  if ($userEmail) {
      $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
      $stmt->bind_param("s", $userEmail);
      $stmt->execute();
      $userResult = $stmt->get_result();
      if ($userResult->num_rows > 0) {
          $user = $userResult->fetch_assoc();
          $userId = $user['User_ID'];

          // 確保是自己的留言才能刪
          $checkComment = $conn->prepare("SELECT * FROM comments WHERE Comment_ID = ? AND User_ID = ?");
          $checkComment->bind_param("ii", $commentId, $userId);
          $checkComment->execute();
          $commentResult = $checkComment->get_result();
          if ($commentResult->num_rows > 0) {
              $deleteComment = $conn->prepare("UPDATE comments SET is_deleted = 1 WHERE Comment_ID = ?");
              $deleteComment->bind_param("i", $commentId);
              $deleteComment->execute();
          }
      }
  }
  header("Location: blog-details.php");
  exit;
}

// 處理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['comment'])) {
  $postId = intval($_POST['post_id']);
  $comment = $conn->real_escape_string(trim(str_replace(["\r", "\n"], "", $_POST['comment']))); // 移除 \r 和 \n
  $expandComments = isset($_POST['expand_comments']) ? intval($_POST['expand_comments']) : 0;

  // 從 SESSION 中取得使用者的 E-mail
  $userEmail = $_SESSION['user'] ?? null;
  if ($userEmail) {
    $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $userId = $user['User_ID'];

      $insertComment = $conn->prepare("INSERT INTO comments (Post_ID, Content, User_ID, Comment_Time) VALUES (?, ?, ?, NOW())");
      $insertComment->bind_param("isi", $postId, $comment, $userId);
      $insertComment->execute();

      // 計算該文章所在的分頁
      $postsPerPage = 5; // ⚠️這要和分頁邏輯中的每頁筆數一致！
      $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId) AND is_deleted = 0");
      $position = $positionResult->fetch_assoc()['position'];
      $page = floor($position / $postsPerPage) + 1;

      // 如果有搜尋參數，保留搜尋結果並定位到該文章，並保持展開狀態
      $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
      $expandParam = $expandComments ? '&expand_comments=1' : '';
      header("Location: blog-details.php?page=$page&highlight_id=$postId$searchParam$expandParam#post-$postId");
      exit;
    } else {
      echo "<script>alert('無法找到對應的使用者資訊，請重新登入');</script>";
    }
  } else {
    echo "<script>alert('用戶未登入，請先登入');</script>";
  }

  // Redirect back to the same page to ensure posts are displayed
  header("Location: blog-details.php");
  exit;
}

// 處理點讚請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postId'])) {
  header('Content-Type: application/json');

  session_start();
  if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '無法取得使用者 ID']);
    exit;
  }

  $postId = intval($_POST['postId']);
  $userId = $_SESSION['user_id']; // 從 session 中獲取 User_ID

  // 插入點讚記錄
  $addLike = $conn->prepare("INSERT INTO likes (User_ID, Post_ID, Like_Time) VALUES (?, ?, NOW())");
  $addLike->bind_param("ii", $userId, $postId);
  if ($addLike->execute()) {
    $likeId = $conn->insert_id; // 獲取 Like_ID

    // 更新文章的點讚數
    $updateLikes = $conn->prepare("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = ?");
    $updateLikes->bind_param("i", $postId);
    $updateLikes->execute();

    // 獲取最新的點讚數
    $stmt = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $likes = $result->fetch_assoc()['Likes'];

    echo json_encode(['success' => true, 'postId' => $postId, 'userId' => $userId, 'likeId' => $likeId, 'likes' => $likes]);
  } else {
    echo json_encode(['success' => false, 'message' => '無法完成點讚操作，請稍後再試']);
  }
  exit;
}

// 分頁邏輯
$postsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $postsPerPage;

$totalPostsQuery = $conn->query("SELECT COUNT(*) as total FROM posts WHERE is_deleted = 0");
$totalPosts = $totalPostsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

$postsQuery = $conn->prepare("
  SELECT p.*, a.Nickname, a.Roles, a.Photo, t.verified
  FROM posts p 
  JOIN account a ON p.User_ID = a.User_ID 
  LEFT JOIN teacher_info t ON a.User_ID = t.account_id 
  WHERE p.is_deleted = 0 
  ORDER BY Post_Time DESC 
  LIMIT ? OFFSET ?
");


$postsQuery->bind_param("ii", $postsPerPage, $offset);
$postsQuery->execute();
$postsResult = $postsQuery->get_result();

// 修改搜尋邏輯，根據部分符合的文章內容、文章標題和文章留言來篩選結果
$searchResults = [];
if (isset($_GET['search'])) {
  $searchTerm = $conn->real_escape_string($_GET['search']);
  $searchQuery = $conn->query(
    "SELECT DISTINCT p.*, a.Nickname 
         FROM posts p 
         JOIN account a ON p.User_ID = a.User_ID 
         LEFT JOIN comments c ON p.Post_ID = c.Post_ID 
         WHERE p.is_deleted = 0 AND (p.Title LIKE '%$searchTerm%' 
            OR p.Content LIKE '%$searchTerm%' 
            OR c.Content LIKE '%$searchTerm%') 
         ORDER BY p.Post_Time DESC"
  );
  while ($row = $searchQuery->fetch_assoc()) {
    // 獲取每篇文章的留言及其點讚數，並按點讚數排序，若點讚數相同則按發布時間排序
    $postId = $row['Post_ID'];
    $commentsQuery = $conn->prepare(
      "SELECT c.*, a.Nickname, 
                    (SELECT COUNT(*) FROM likes WHERE Comment_ID = c.Comment_ID) AS Likes 
             FROM comments c 
             JOIN account a ON c.User_ID = a.User_ID 
             WHERE c.Post_ID = ? AND c.is_deleted = 0 
             ORDER BY Likes DESC, c.Comment_Time ASC"
    );
    $commentsQuery->bind_param("i", $postId);
    $commentsQuery->execute();
    $commentsResult = $commentsQuery->get_result();
    $row['comments'] = $commentsResult->fetch_all(MYSQLI_ASSOC);
    $searchResults[] = $row;
  }
}

// 從 SESSION 中取得使用者的 Nickname
$nickname = "訪客";
$role = "";

if (isset($_SESSION['user'])) {
    $userEmail = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT Nickname, Roles FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $nickname = $user['Nickname'];
        $role = $user['Roles'];

        // ✅ Store into session for later use
        $_SESSION['user_role'] = $role;
        $_SESSION['nickname'] = $nickname;
    }
    $stmt->close();
}


// 從 SESSION 中取得使用者的 Photo
$photo = "assets/img/personal_photo/default.jpeg"; // 預設圖片
if (isset($_SESSION['user'])) {
  $userEmail = $_SESSION['user'];
  $stmt = $conn->prepare("SELECT Photo FROM account WHERE `E-mail` = ?");
  $stmt->bind_param("s", $userEmail);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $photo = !empty($user['Photo']) && file_exists($user['Photo']) ? $user['Photo'] : $photo;
  }
  $stmt->close();
}


// 獲取使用者的近期貼文
$recentPosts = [];
if (isset($_SESSION['user'])) {
  $userEmail = $_SESSION['user'];
  $stmt = $conn->prepare("SELECT p.Title, p.Post_Time, p.Post_ID, p.Content FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE a.`E-mail` = ? AND p.is_deleted = 0 ORDER BY p.Post_Time DESC LIMIT 5");
  $stmt->bind_param("s", $userEmail);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    // 計算該貼文所在的分頁
    $postId = $row['Post_ID'];
    $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId) AND is_deleted = 0");  // 這裡已經有is_deleted = 0
    $position = $positionResult->fetch_assoc()['position'];
    $page = floor($position / $postsPerPage) + 1;

    $row['page'] = $page; // 將分頁資訊加入結果
    $recentPosts[] = $row;
  }
  $stmt->close();
}

// 獲取使用者的近期留言
$recentComments = [];
if (isset($_SESSION['user'])) {
  $userEmail = $_SESSION['user'];
  $stmt = $conn->prepare("SELECT c.Content, c.Comment_Time, p.Title, p.Post_ID, c.Comment_ID FROM comments c JOIN posts p ON c.Post_ID = p.Post_ID JOIN account a ON c.User_ID = a.User_ID WHERE a.`E-mail` = ? AND c.is_deleted = 0 ORDER BY c.Comment_Time DESC LIMIT 5");
  $stmt->bind_param("s", $userEmail);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    // 計算該留言所在的文章分頁
    $postId = $row['Post_ID'];
    $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId) AND is_deleted = 0");  // 這裡已經有is_deleted = 0
    $position = $positionResult->fetch_assoc()['position'];
    $page = floor($position / $postsPerPage) + 1;

    $row['page'] = $page; // 將分頁資訊加入結果
    $recentComments[] = $row;
  }
  $stmt->close();
}
?>

<script>
  // 修正查看更多和顯示更多留言的功能，確保回復原本按鈕和顯示更多留言正常運作
  function showFullContent(link, fullContent) {
    const parent = link.closest('.short-content');
    const originalContent = parent.innerHTML; // 保存原始內容
    parent.dataset.originalContent = originalContent; // 使用 data 屬性保存原始內容
    parent.innerHTML = fullContent + '<br><button class="btn btn-link" onclick="restoreContent(this)">(收回)</button>';
  }

  function restoreContent(button) {
    const parent = button.closest('.short-content');
    const originalContent = parent.dataset.originalContent; // 從 data 屬性中取回原始內容
    parent.innerHTML = originalContent;
  }

  function showMoreComments(button, postId) {
    const allComments = document.getElementById(`all-comments-${postId}`);
    const topComments = document.getElementById(`top-comments-${postId}`);

    // 保存原始的 topComments 狀態
    if (!allComments.dataset.originalTopComments) {
      allComments.dataset.originalTopComments = topComments.innerHTML;
    }

    // 顯示所有留言
    allComments.style.display = 'block';
    button.style.display = 'none';

    // 添加回復原本按鈕
    const restoreButton = document.createElement('button');
    restoreButton.className = 'btn btn-link';
    restoreButton.textContent = '(收回)';
    restoreButton.onclick = function() {
      allComments.style.display = 'none';
      button.style.display = 'block';
      topComments.innerHTML = allComments.dataset.originalTopComments; // 恢復原始的 topComments 狀態
      restoreButton.remove();
    };
    allComments.parentNode.appendChild(restoreButton);

    // 防止留言區在新增留言後自動收回
    const commentForm = allComments.parentNode.querySelector('form');
    if (commentForm && !commentForm.dataset.preventCollapse) {
      commentForm.dataset.preventCollapse = true; // 確保只綁定一次事件
      commentForm.addEventListener('submit', function() {
        allComments.style.display = 'block'; // 保持展開狀態
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-like').forEach(function(button) {
      button.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const commentId = this.getAttribute('data-comment-id');

        if (postId || commentId) {
          fetch('like-handler.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                postId: postId || null,
                commentId: commentId || null
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                if (data.action === 'liked') {
                  this.classList.add('liked');
                  this.querySelector('i').classList.remove('bi-heart');
                  this.querySelector('i').classList.add('bi-heart-fill');
                } else if (data.action === 'unliked') {
                  this.classList.remove('liked');
                  this.querySelector('i').classList.remove('bi-heart-fill');
                  this.querySelector('i').classList.add('bi-heart');
                }
                this.querySelector('span').textContent = data.likes;
              } else {
                alert(data.message || '操作失敗，請稍後再試');
              }
            })
            .catch(error => console.error('Error:', error));
        }
      });
    });

    // 更新文章按鈕狀態
    likedPostIds.forEach(postId => {
      const postButton = document.querySelector(`.btn-like[data-post-id="${postId}"]`);
      if (postButton) {
        postButton.classList.add('liked');
        postButton.querySelector('i').classList.remove('bi-heart');
        postButton.querySelector('i').classList.add('bi-heart-fill');
      }
    });

    // 更新留言按鈕狀態
    likedCommentIds.forEach(commentId => {
      const commentButton = document.querySelector(`.btn-like[data-comment-id="${commentId}"]`);
      if (commentButton) {
        commentButton.classList.add('liked');
        commentButton.querySelector('i').classList.remove('bi-heart');
        commentButton.querySelector('i').classList.add('bi-heart-fill');
      }
    });
  });
</script>

<!-- 捲動-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight_id');

    if (highlightId) {
      const interval = setInterval(() => {
        const target = document.querySelector('[data-post-id="' + highlightId + '"]');
        if (target) {
          const yOffset = -400; // 增加偏移量，確保內容在 header 下方
          const y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
          window.scrollTo({
            top: y,
            behavior: 'smooth'
          });
          target.classList.add('highlighted-post');
          clearInterval(interval);
        }
      }, 200);

      // 最多等 3 秒
      setTimeout(() => clearInterval(interval), 3000);
    }
  });
</script>


<style>
  .highlighted-post {
    border: 2px solid rgb(140, 174, 213) !important;
    background-color: #fff8e1 !important;
    transition: all 0.5s ease;
  }


  .role {
    font-style: ;
    color: rgb(79, 81, 81);
    /* 橘色 */
    font-size: 0.85em !important;

    border-radius: 4px;
    padding: 2px 4px;
    background-color: rgb(211, 211, 227);
    /* 淡橘背景 */
  }
</style>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Blog Details - Moderna Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Moderna
  * Template URL: https://bootstrapmade.com/free-bootstrap-template-corporate-moderna/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
  <style>
    .post-item {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .comment-item {
      border-top: 1px solid #ddd;
      padding-top: 10px;
      margin-top: 10px;
    }

    .btn-like {
      background: none;
      border: none;
      color: #007bff;
      font-size: 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
    }

    .btn-like i {
      margin-right: 5px;
    }

    .btn-like:hover {
      color: #0056b3;
    }

    .post-item .meta {
      font-size: 0.9em;
      color: #6c757d;
    }

    .comment-item .meta {
      font-size: 0.8em;
      color: #6c757d;
    }

    .post-item p {
      font-size: 1.5em;
      /* 放大 50% */
      color: #212529;
    }

    .comment-item p {
      font-size: 1.35em;
      /* 放大 50% */
      color: #212529;
    }

    .post-item h3 {
      font-size: 3em;
      /* 放大 100% */
    }

    .floating-btn {
      margin-top: 15px;
      display: inline-block;
      background: #007bff;
      /* 純藍色背景 */
      color: #fff;
      border: none;
      border-radius: 20px;
      padding: 10px 20px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .floating-btn:hover {
      background: #0056b3;
      /* 深藍色背景 */
      transform: scale(1.05);
    }

    .floating-btn i {
      margin-left: 5px;
    }

    .post-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 10px;
    }

    .avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
    }

    .post-title {
      font-size: 1.25rem;
      font-weight: bold;
      margin: 0;
    }
    #dlt {
      float:right;
    }
  </style>
</head>

<body class="blog-details-page">



  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1>討論區</h1>
        <p>在這裡分享你的想法，並與他人互動</p>

      </div>
    </div><!-- End Page Title -->

    <div class="container">
      <div class="row">

        <div class="col-lg-8">

          <!-- 搜尋結果 -->
          <?php if (!empty($searchResults)): ?>

            <h2>搜尋結果:</h2>

            <?php foreach ($searchResults as $post): ?>
              <?php
              $alreadyLiked = in_array($post['Post_ID'], $likedPostIds);
              $comments = $post['comments'];
              $topComments = array_slice($comments, 0, 3);
              $content = htmlspecialchars($post['Content']);
              ?>

              <div class="post-item" data-post-id="<?= $post['Post_ID'] ?>">
                <h3><?= htmlspecialchars($post['Title']) ?></h3>
                <div class="meta">
                  <span>由 <?= htmlspecialchars($post['Nickname']) ?> 發布於 <?= $post['Post_Time'] ?></span>
                </div>
                <?php if (mb_strlen($content) > 300): ?>
                  <p class="short-content">
                    <?= nl2br(mb_substr($content, 0, 75)) ?>...
                    <a href="#" class="read-more" onclick="showFullContent(this, '<?= addslashes($content) ?>'); return false;">(查看更多)</a>
                  </p>
                <?php else: ?>
                  <p><?= nl2br($content) ?></p>
                <?php endif; ?>
                <button
                  class="btn-like <?= $alreadyLiked ? 'liked' : '' ?>"
                  data-post-id="<?= $post['Post_ID'] ?>">
                  <i class="bi bi-heart"></i> <span><?= $post['Likes'] ?></span>
                </button>

                <!-- 顯示留言 -->
                <div class="comments">
                  <h4>留言區</h4>
                  <div id="top-comments-<?= $post['Post_ID'] ?>">
                    <?php foreach ($topComments as $comment): ?>
                      <div class="comment-item">
                        <div class="d-flex align-items-start gap-3">
                          <?php
                          $avatarPath = !empty($comment['Photo']) && file_exists($comment['Photo'])
                            ? $comment['Photo']
                            : 'assets/img/personal_photo/default.jpeg';
                          ?>
                          <img src="<?= htmlspecialchars($avatarPath) ?>" class="avatar" alt="留言者頭像" style="width: 40px; height: 40px;">
                          <div>
                            <?php
                            $roleText = $comment['Roles'];
                            if ($roleText === '教師') {
                              $roleText = ($comment['verified']) ? '教師(已驗證)' : '教師(尚未驗證)';
                            }
                            ?>
                            <p>
                              <strong><?= htmlspecialchars($comment['Nickname']) ?></strong>
                              <span class="role"><?= htmlspecialchars($roleText) ?></span>:
                              <?= nl2br(htmlspecialchars($comment['Content'])) ?>
                            </p>
                            <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                            <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                              <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                            </button>
                            <?php if (
                              isset($_SESSION['user_id']) &&
                              ($comment['User_ID'] === $_SESSION['user_id'] || $_SESSION['user_role'] === '管理者')
                            ): ?>
                              <form method="POST" action="blog-details.php" onsubmit="return confirm('確定要刪除這則留言嗎？');" style="display:inline;">
                                <input type="hidden" name="delete_comment_id" value="<?= $comment['Comment_ID'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                              </form>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <?php if (count($comments) > 3): ?>
                    <button id="show-more-comments" class="btn btn-link" onclick="showMoreComments(this, <?= $post['Post_ID'] ?>)">顯示更多留言</button>
                    <div id="all-comments-<?= $post['Post_ID'] ?>" style="display: none;">
                      <?php foreach (array_slice($comments, 3) as $comment): ?>
                        <div class="comment-item">
                          <div class="d-flex align-items-start gap-3">
                            <?php
                            $avatarPath = !empty($comment['Photo']) && file_exists($comment['Photo'])
                              ? $comment['Photo']
                              : 'assets/img/personal_photo/default.jpeg';
                            ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>" class="avatar" alt="留言者頭像" style="width: 40px; height: 40px;">
                            <div>
                              <?php
                              $roleText = $comment['Roles'];
                              if ($roleText === '教師') {
                                $roleText = ($comment['verified']) ? '教師(已驗證)' : '教師(尚未驗證)';
                              }
                              ?>
                              <p>
                                <strong><?= htmlspecialchars($comment['Nickname']) ?></strong>
                                <span class="role"><?= htmlspecialchars($roleText) ?></span>:
                                <?= nl2br(htmlspecialchars($comment['Content'])) ?>
                              </p>
                              <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                              <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                                <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                              </button>
                              <?php if (
                                isset($_SESSION['user_id']) &&
                                ($comment['User_ID'] === $_SESSION['user_id'] || $_SESSION['user_role'] === '管理者')
                              ): ?>
                                <form method="POST" action="blog-details.php" onsubmit="return confirm('確定要刪除這則留言嗎？');" style="display:inline;">
                                  <input type="hidden" name="delete_comment_id" value="<?= $comment['Comment_ID'] ?>">
                                  <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                </form>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- 新增留言表單 -->
                <form method="POST" action="">
                  <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
                  <input type="hidden" name="expand_comments" value="1">
                  <div class="mb-3">
                    <textarea name="comment" class="form-control" placeholder="新增留言..." required></textarea>
                  </div>
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">送出留言</button>
                  </div>
                </form>
              </div>
            <?php endforeach; ?>

          <?php else: ?>

            <?php
            // 取得目前登入的用戶 ID

            // 🔍 查詢目前用戶已經點過讚的文章
            $likedPostIds = [];
            $likedQuery = $conn->prepare("SELECT post_id FROM likes WHERE user_id = ?");
            $likedQuery->bind_param("i", $user_id);
            $likedQuery->execute();
            $likedResult = $likedQuery->get_result();
            while ($row = $likedResult->fetch_assoc()) {
              $likedPostIds[] = $row['post_id'];
            }
            ?>
            <!-- 顯示貼文 -->
            <section id="blog-posts" class="blog-posts section">
              <div class="container">
                <?php while ($post = $postsResult->fetch_assoc()): ?>
                  <div class="post-item data-post-id=" <?= $post['Post_ID'] ?>" id="post-<?= $post['Post_ID'] ?>">
                  <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === '管理者'): ?>
  <form method="POST" action="delete-post.php" style="display:inline;">
    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['Post_ID']) ?>">
    <button id="dlt" type="submit" class="btn btn-danger btn-sm" onclick="return confirm('確定要刪除這篇貼文嗎？')">
      刪除貼文
    </button>
  </form>
<?php endif; ?>
                    <?php
                    $avatarPath = !empty($post['Photo']) && file_exists($post['Photo'])
                      ? $post['Photo']
                      : 'assets/img/personal_photo/default.jpeg';
                    ?>
                    <div class="post-header">
                      <img src="<?= htmlspecialchars($avatarPath) ?>" class="avatar" alt="作者頭像">
                      <h3><?= htmlspecialchars($post['Title']) ?></h3>
                    </div>


                    <div class="meta">

                      <?php
                      $roleText = $post['Roles'];
                      if ($roleText === '教師') {
                        $roleText = ($post['verified']) ? '教師(已驗證)' : '教師(尚未驗證)';
                      }
                      ?>

                      <span>由 <?= htmlspecialchars($post['Nickname']) ?>
                        <span class="role"><?= htmlspecialchars($roleText) ?></span>
                        發布於 <?= $post['Post_Time'] ?></span>
                    </div>
                    <?php
                    $content = htmlspecialchars($post['Content']);
                    if (mb_strlen($content) > 30): ?>
                      <p class="short-content">
                        <?= nl2br(mb_substr($content, 0, 75)) ?>...
                        <a href="#" class="read-more" onclick="showFullContent(this, '<?= addslashes($content) ?>'); return false;">(查看更多)</a>
                      </p>
                    <?php else: ?>
                      <p><?= nl2br($content) ?></p>
                    <?php endif; ?>
                    <?php $alreadyLiked = in_array($post['Post_ID'], $likedPostIds); ?>


                    <button
                      class="btn-like <?= $alreadyLiked ? 'liked' : '' ?>"
                      data-post-id="<?= $post['Post_ID'] ?>"
                      <?= $alreadyLiked ? 'disabled' : '' ?>>
                      <i class="bi bi-heart"></i> <span><?= $post['Likes'] ?></span>
                    </button>

                    <!-- 顯示留言 -->
                    <div class="comments">
                      <h4>留言區</h4>
                      <?php
                      $commentsQuery = $conn->prepare("
                      SELECT c.*, a.Nickname, a.Roles, a.Photo, t.verified 
                      FROM comments c 
                      JOIN account a ON c.User_ID = a.User_ID 
                      LEFT JOIN teacher_info t ON a.User_ID = t.account_id 
                      WHERE c.Post_ID = ? AND c.is_deleted = 0 
                      ORDER BY c.Likes DESC, c.Comment_Time ASC
                      ");
                      $commentsQuery->bind_param("i", $post['Post_ID']);
                      $commentsQuery->execute();
                      $commentsResult = $commentsQuery->get_result();
                      $comments = [];
                      while ($comment = $commentsResult->fetch_assoc()) {
                        $comments[] = $comment;
                      }
                      $topComments = array_slice($comments, 0, 3);
                      ?>

                      <div id="top-comments-<?= $post['Post_ID'] ?>">
                        <?php foreach ($topComments as $comment): ?>
                          <div class="comment-item">
                            <div class="d-flex align-items-start gap-3">
                              <?php
                              $avatarPath = !empty($comment['Photo']) && file_exists($comment['Photo'])
                                ? $comment['Photo']
                                : 'assets/img/personal_photo/default.jpeg';
                              ?>
                              <img src="<?= htmlspecialchars($avatarPath) ?>" class="avatar" alt="留言者頭像" style="width: 40px; height: 40px;">
                              <div>
                                <?php
                                $roleText = $comment['Roles'];
                                if ($roleText === '教師') {
                                  $roleText = ($comment['verified']) ? '教師(已驗證)' : '教師(尚未驗證)';
                                }
                                ?>
                                <p>
                                  <strong><?= htmlspecialchars($comment['Nickname']) ?></strong>
                                  <span class="role"><?= htmlspecialchars($roleText) ?></span>:
                                  <?= nl2br(htmlspecialchars($comment['Content'])) ?>
                                </p>
                                <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                                <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                                  <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                                </button>
                                <?php if (
                                  isset($_SESSION['user_id']) &&
                                  ($comment['User_ID'] === $_SESSION['user_id'] || $_SESSION['user_role'] === '管理者')
                                ): ?>
                                  <form method="POST" action="blog-details.php" onsubmit="return confirm('確定要刪除這則留言嗎？');" style="display:inline;">
                                    <input type="hidden" name="delete_comment_id" value="<?= $comment['Comment_ID'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                  </form>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>

                      <?php if (count($comments) > 3): ?>
                        <button id="show-more-comments" class="btn btn-link" onclick="showMoreComments(this, <?= $post['Post_ID'] ?>)">顯示更多留言</button>
                        <div id="all-comments-<?= $post['Post_ID'] ?>" style="display: none;">
                          <?php foreach (array_slice($comments, 3) as $comment): ?>
                            <div class="comment-item">
                              <div class="d-flex align-items-start gap-3">
                                <?php
                                $avatarPath = !empty($comment['Photo']) && file_exists($comment['Photo'])
                                  ? $comment['Photo']
                                  : 'assets/img/personal_photo/default.jpeg';
                                ?>
                                <img src="<?= htmlspecialchars($avatarPath) ?>" class="avatar" alt="留言者頭像" style="width: 40px; height: 40px;">
                                <div>
                                  <?php
                                  $roleText = $comment['Roles'];
                                  if ($roleText === '教師') {
                                    $roleText = ($comment['verified']) ? '教師(已驗證)' : '教師(尚未驗證)';
                                  }
                                  ?>
                                  <p>
                                    <strong><?= htmlspecialchars($comment['Nickname']) ?></strong>
                                    <span class="role"><?= htmlspecialchars($roleText) ?></span>:
                                    <?= nl2br(htmlspecialchars($comment['Content'])) ?>
                                  </p>
                                  <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                                  <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                                    <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                                  </button>
                                  <?php if (
                                    isset($_SESSION['user_id']) &&
                                    ($comment['User_ID'] === $_SESSION['user_id'] || $_SESSION['user_role'] === '管理者')
                                  ): ?>
                                    <form method="POST" action="blog-details.php" onsubmit="return confirm('確定要刪除這則留言嗎？');" style="display:inline;">
                                      <input type="hidden" name="delete_comment_id" value="<?= $comment['Comment_ID'] ?>">
                                      <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                    </form>
                                  <?php endif; ?>
                                </div>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- 新增留言表單 -->
                    <form method="POST" action="">
                      <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
                      <input type="hidden" name="expand_comments" value="1">
                      <div class="mb-3">
                        <textarea name="comment" class="form-control" placeholder="新增留言..." required></textarea>
                      </div>
                      <div class="text-end">
                        <button type="submit" class="btn btn-primary">送出留言</button>
                      </div>
                    </form>
                  </div>
                <?php endwhile; ?>

                <!-- 分頁導航 -->
                <?php if ($totalPages > 1): ?>
                  <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                      <?php if ($page > 1): ?>
                        <li class="page-item">
                          <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                          </a>
                        </li>
                      <?php endif; ?>

                      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                      <?php endfor; ?>

                      <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                          <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                          </a>
                        </li>
                      <?php endif; ?>
                    </ul>
                  </nav>
                <?php endif; ?>
              </div>
            </section>

          <?php endif; ?>

        </div>

        <div class="col-lg-4 sidebar">

          <div class="widgets-container">

            <!-- Blog Author Widget -->
            <div class="blog-author-widget widget-item">

              <div class="d-flex flex-column align-items-center">
                <img src="<?= htmlspecialchars(!empty($photo) ? $photo : 'assets/img/personal_photo/default.jpeg') ?>" class="rounded-circle flex-shrink-0" alt="">
                <h4><?= htmlspecialchars($nickname) ?></h4>
              </div>
            </div><!--/Blog Author Widget -->
            <button type="button" class="btn btn-primary floating-btn" data-bs-toggle="modal" data-bs-target="#commentModal">
              <span>按此新增貼文</span> <i class="bi bi-plus"></i>
            </button>

            <!-- 增加空白間距 -->
            <div style="height: 20px;"></div>

            <!-- Search Widget -->
            <div class="search-widget widget-item">

              <h3 class="widget-title">Search</h3>
              <form action="" method="GET">
                <input type="text" name="search" placeholder="搜尋標題、內容、留言" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
              </form>
            </div><!--/Search Widget -->

            <!-- 浮動式按鈕 -->

            <!-- 彈跳視窗 -->
            <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="commentModalLabel">發布貼文</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form method="POST" action="">
                      <div class="mb-3">
                        <input type="text" name="title" class="form-control" placeholder="輸入你的標題" required>
                      </div>
                      <div class="mb-3">
                        <textarea name="comment" class="form-control" placeholder="輸入你的內容" required></textarea>
                      </div>
                      <div class="text-center">
                        <button type="submit" class="btn btn-primary">發送</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <style>
              .floating-btn {
                margin-top: 15px;
                display: inline-block;
                background: #007bff;
                /* 純藍色背景 */
                color: #fff;
                border: none;
                border-radius: 20px;
                padding: 10px 20px;
                font-size: 16px;
                transition: all 0.3s ease;
              }

              .floating-btn:hover {
                background: #0056b3;
                /* 深藍色背景 */
                transform: scale(1.05);
              }

              .floating-btn i {
                margin-left: 5px;
              }
            </style>

            <!-- Modal for all posts -->
            <div class="modal fade" id="allPostsModal" tabindex="-1" aria-labelledby="allPostsModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="allPostsModalLabel">所有文章</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <?php
                    // Fetch all posts by the user
                    $allPosts = [];
                    if (isset($_SESSION['user'])) {
                      $userEmail = $_SESSION['user'];
                      $stmt = $conn->prepare("SELECT p.Title, p.Content, p.Post_Time, p.Post_ID FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE a.`E-mail` = ? AND p.is_deleted = 0 ORDER BY p.Post_Time DESC");
                      $stmt->bind_param("s", $userEmail);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      while ($row = $result->fetch_assoc()) {
                        // Calculate the page number for each post
                        $postId = $row['Post_ID'];
                        $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId) AND is_deleted = 0");
                        $position = $positionResult->fetch_assoc()['position'];
                        $page = floor($position / $postsPerPage) + 1;

                        $row['page'] = $page; // Add page info to the result
                        $allPosts[] = $row;
                      }
                      $stmt->close();
                    }
                    ?>

                    <?php foreach ($allPosts as $post): ?>
                      <div class="post-item">
                        <h5>
                          <a href="blog-details.php?page=<?= urlencode($post['page']) ?>&highlight_id=<?= urlencode($post['Post_ID']) ?>">
                            <?= htmlspecialchars($post['Title'], ENT_QUOTES, 'UTF-8') ?>
                          </a>
                          <button type="button" class="btn btn-link edit-post-btn" data-post-id="<?= $post['Post_ID'] ?>" data-title="<?= htmlspecialchars($post['Title'], ENT_QUOTES, 'UTF-8') ?>" data-content="<?= htmlspecialchars($post['Content'], ENT_QUOTES, 'UTF-8') ?>">修改</button>
                          <button type="button" class="btn btn-link text-danger delete-post-btn" data-post-id="<?= $post['Post_ID'] ?>">刪除</button>
                        </h5>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Edit Post Modal -->
            <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editPostModalLabel">修改貼文</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form method="POST" action="edit-post.php">
                      <input type="hidden" name="post_id" id="edit-post-id">
                      <div class="mb-3">
                        <label for="edit-title" class="form-label">標題</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                      </div>
                      <div class="mb-3">
                        <label for="edit-content" class="form-label">內容</label>
                        <textarea class="form-control" id="edit-content" name="content" rows="4" required></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary">保存修改</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete Post Modal -->
            <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="deletePostModalLabel">確認刪除貼文</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    您確定要刪除此貼文嗎？此操作無法復原。
                  </div>
                  <div class="modal-footer">
                    <form method="POST" action="delete-post.php">
                      <input type="hidden" name="post_id" id="delete-post-id">
                      <button type="submit" class="btn btn-danger">確認刪除</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                  </div>
                </div>
              </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
              // 編輯貼文按鈕點擊事件
              document.querySelectorAll('.edit-post-btn').forEach(button => {
                button.addEventListener('click', function() {
                  const postId = this.dataset.postId;
                  const title = this.dataset.title;
                  const content = this.dataset.content;
                  
                  document.getElementById('edit-post-id').value = postId;
                  document.getElementById('edit-title').value = title;
                  document.getElementById('edit-content').value = content;
                  
                  const editModal = new bootstrap.Modal(document.getElementById('editPostModal'));
                  editModal.show();
                });
              });

              // 刪除貼文按鈕點擊事件
              document.querySelectorAll('.delete-post-btn').forEach(button => {
                button.addEventListener('click', function() {
                  const postId = this.dataset.postId;
                  document.getElementById('delete-post-id').value = postId;
                  
                  const deleteModal = new bootstrap.Modal(document.getElementById('deletePostModal'));
                  deleteModal.show();
                });
              });
            });
            </script>

            <!-- Recent Posts and Comments Widget -->
            <div class="recent-posts-widget widget-item">

              <h4>近期文章</h4>
              <?php if (!empty($recentPosts)): ?>
                <?php foreach ($recentPosts as $post): ?>
                  <div class="post-item">
                    <div>
                      <h5>
                        <a href="blog-details.php?page=<?= $post['page'] ?>&highlight_id=<?= $post['Post_ID'] ?>">
                          <?= htmlspecialchars($post['Title']) ?>
                        </a>
                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editPostModal-<?= $post['Post_ID'] ?>">修改</button>
                        <button type="button" class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal-<?= $post['Post_ID'] ?>">刪除</button>
                      </h5>
                    </div>
                  </div>

                  <!-- Modal for editing post -->
                  <div class="modal fade" id="editPostModal-<?= $post['Post_ID'] ?>" tabindex="-1" aria-labelledby="editPostModalLabel-<?= $post['Post_ID'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editPostModalLabel-<?= $post['Post_ID'] ?>">修改貼文</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <form method="POST" action="edit-post.php">
                            <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
                            <div class="mb-3">
                              <label for="title-<?= $post['Post_ID'] ?>" class="form-label">標題</label>
                              <input type="text" class="form-control" id="title-<?= $post['Post_ID'] ?>" name="title" value="<?= htmlspecialchars($post['Title']) ?>" required>
                            </div>
                            <div class="mb-3">
                              <label for="content-<?= $post['Post_ID'] ?>" class="form-label">內容</label>
                              <textarea class="form-control" id="content-<?= $post['Post_ID'] ?>" name="content" rows="4" required><?= htmlspecialchars($post['Content']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">保存修改</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Modal for deleting post -->
                  <div class="modal fade" id="deletePostModal-<?= $post['Post_ID'] ?>" tabindex="-1" aria-labelledby="deletePostModalLabel-<?= $post['Post_ID'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deletePostModalLabel-<?= $post['Post_ID'] ?>">確認刪除貼文</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          您確定要刪除此貼文嗎？此操作無法復原。
                        </div>
                        <div class="modal-footer">
                          <form method="POST" action="delete-post.php">
                            <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
                            <button type="submit" class="btn btn-danger">確認刪除</button>
                          </form>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>尚未發布任何文章。</p>
              <?php endif; ?>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allPostsModal">
                查看全部貼文
              </button>


              <h4>近期留言</h4>
              <?php if (!empty($recentComments)): ?>
                <?php foreach ($recentComments as $comment): ?>
                  <div class="post-item">
                    <div>
                      <p>
                        留言於文章:
                        <strong>
                          <a href="blog-details.php?page=<?= $comment['page'] ?>&highlight_id=<?= $comment['Post_ID'] ?>">
                            <?= htmlspecialchars($comment['Title']) ?>
                          </a>
                        </strong>
                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editCommentModal-<?= $comment['Comment_ID'] ?>">修改</button>
                        <button type="button" class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#deleteCommentModal-<?= $comment['Comment_ID'] ?>">刪除</button>
                      </p>
                    </div>
                  </div>

                  <!-- Modal for editing comment -->
                  <div class="modal fade" id="editCommentModal-<?= $comment['Comment_ID'] ?>" tabindex="-1" aria-labelledby="editCommentModalLabel-<?= $comment['Comment_ID'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editCommentModalLabel-<?= $comment['Comment_ID'] ?>">修改留言</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <form method="POST" action="edit-comment.php">
                            <input type="hidden" name="comment_id" value="<?= $comment['Comment_ID'] ?>">
                            <div class="mb-3">
                              <label for="content-<?= $comment['Comment_ID'] ?>" class="form-label">內容</label>
                              <textarea class="form-control" id="content-<?= $comment['Comment_ID'] ?>" name="content" rows="4" required><?= htmlspecialchars($comment['Content']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">保存修改</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Modal for deleting comment -->
                  <div class="modal fade" id="deleteCommentModal-<?= $comment['Comment_ID'] ?>" tabindex="-1" aria-labelledby="deleteCommentModalLabel-<?= $comment['Comment_ID'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deleteCommentModalLabel-<?= $comment['Comment_ID'] ?>">確認刪除留言</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          您確定要刪除此留言嗎？此操作無法復原。
                        </div>
                        <div class="modal-footer">
                          <form method="POST" action="delete-comment.php">
                            <input type="hidden" name="comment_id" value="<?= $comment['Comment_ID'] ?>">
                            <button type="submit" class="btn btn-danger">確認刪除</button>
                          </form>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>尚未發布任何留言。</p>
              <?php endif; ?>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allCommentsModal">
                查看全部留言
              </button>

              <!-- Modal for all comments -->
              <div class="modal fade" id="allCommentsModal" tabindex="-1" aria-labelledby="allCommentsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="allCommentsModalLabel">所有留言</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <?php
                      // Fetch all comments by the user
                      $allComments = [];
                      if (isset($_SESSION['user'])) {
                        $userEmail = $_SESSION['user'];
                        $stmt = $conn->prepare("SELECT c.Comment_ID, c.Content, c.Comment_Time, p.Title, p.Post_ID FROM comments c JOIN posts p ON c.Post_ID = p.Post_ID JOIN account a ON c.User_ID = a.User_ID WHERE a.`E-mail` = ? AND c.is_deleted = 0 ORDER BY c.Comment_Time DESC");
                        $stmt->bind_param("s", $userEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                          // Calculate the page number for each post
                          $postId = $row['Post_ID'];
                          $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId) AND is_deleted = 0");
                          $position = $positionResult->fetch_assoc()['position'];
                          $page = floor($position / $postsPerPage) + 1;

                          $row['page'] = $page; // Add page info to the result
                          $allComments[] = $row;
                        }
                        $stmt->close();
                      }
                      ?>

                      <?php foreach ($allComments as $comment): ?>
                        <div class="post-item">
                          <h5>
                            <a href="blog-details.php?page=<?= urlencode($comment['page']) ?>&highlight_id=<?= urlencode($comment['Post_ID']) ?>">
                              <?= htmlspecialchars($comment['Title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editCommentModal-<?= $comment['Comment_ID'] ?>">修改</button>
                            <button type="button" class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#deleteCommentModal-<?= $comment['Comment_ID'] ?>">刪除</button>
                          </h5>
                        </div>

                        <!-- Modal for editing comment -->
                        <div class="modal fade" id="editCommentModal-<?= $comment['Comment_ID'] ?>" tabindex="-1" aria-labelledby="editCommentModalLabel-<?= $comment['Comment_ID'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="editCommentModalLabel-<?= $comment['Comment_ID'] ?>">修改留言</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <form method="POST" action="edit-comment.php">
                                  <input type="hidden" name="comment_id" value="<?= $comment['Comment_ID'] ?>">
                                  <div class="mb-3">
                                    <label for="content-<?= $comment['Comment_ID'] ?>" class="form-label">內容</label>
                                    <textarea class="form-control" id="content-<?= $comment['Comment_ID'] ?>" name="content" rows="4" required><?= htmlspecialchars($comment['Content']) ?></textarea>
                                  </div>
                                  <button type="submit" class="btn btn-primary">保存修改</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Modal for deleting comment -->
                        <div class="modal fade" id="deleteCommentModal-<?= $comment['Comment_ID'] ?>" tabindex="-1" aria-labelledby="deleteCommentModalLabel-<?= $comment['Comment_ID'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="deleteCommentModalLabel-<?= $comment['Comment_ID'] ?>">確認刪除留言</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                您確定要刪除此留言嗎？此操作無法復原。
                              </div>
                              <div class="modal-footer">
                                <form method="POST" action="delete-comment.php">
                                  <input type="hidden" name="comment_id" value="<?= $comment['Comment_ID'] ?>">
                                  <button type="submit" class="btn btn-danger">確認刪除</button>
                                </form>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div><!--/Recent Posts and Comments Widget -->

            

          </div>

        </div>

      </div>
    </div>

  </main>

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