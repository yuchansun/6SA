<?php include('header.php'); ?>


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
    $content = $conn->real_escape_string(trim($_POST['comment']));

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

// 處理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['comment'])) {
    $postId = intval($_POST['post_id']);
    $comment = $conn->real_escape_string(trim($_POST['comment']));
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

            // 如果有搜尋參數，保留搜尋結果並定位到該文章，並保持展開狀態
            $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
            $expandParam = $expandComments ? '&expand_comments=1' : '';
            header("Location: blog-details.php?post_id=$postId$searchParam$expandParam#post-$postId");
            exit;
        } else {
            echo "<script>alert('無法找到對應的使用者資訊，請重新登入');</script>";
        }
    } else {
        echo "<script>alert('用戶未登入，請先登入');</script>";
    }
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

$totalPostsQuery = $conn->query("SELECT COUNT(*) as total FROM posts");
$totalPosts = $totalPostsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

$postsQuery = $conn->prepare("SELECT p.*, a.Nickname, a.Roles FROM posts p JOIN account a ON p.User_ID = a.User_ID ORDER BY Post_Time DESC LIMIT ? OFFSET ?");
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
         WHERE p.Title LIKE '%$searchTerm%' 
            OR p.Content LIKE '%$searchTerm%' 
            OR c.Content LIKE '%$searchTerm%' 
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
             WHERE c.Post_ID = ? 
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
$nickname = "訪客"; // 預設值
if (isset($_SESSION['user'])) {
    $userEmail = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT Nickname FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $nickname = $user['Nickname'];
    }
    $stmt->close();
}

// 從 SESSION 中取得使用者的 Photo
$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : "assets/img/personal_photo/default.jpeg"; // 如果 SESSION 中有 photo，使用該值，否則使用預設圖片

// 獲取使用者的近期貼文
$recentPosts = [];
if (isset($_SESSION['user'])) {
    $userEmail = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT p.Title, p.Post_Time FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE a.`E-mail` = ? ORDER BY p.Post_Time DESC LIMIT 5");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recentPosts[] = $row;
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
    restoreButton.onclick = function () {
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
        commentForm.addEventListener('submit', function () {
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

<!-- 自動捲動並加上黃色邊框與背景 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const params = new URLSearchParams(window.location.search);
  const highlightId = params.get('highlight_id');
  if (highlightId) {
    const target = document.querySelector('[data-post-id="' + highlightId + '"]');
    if (target) {
      // ➤ 計算位置並手動捲動，加上 offset 以避開 header
      const yOffset = -370; // ← 這裡調整 header 高度，建議先設 100~120
      const y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
      window.scrollTo({ top: y, behavior: 'smooth' });

      // ➤ 加上高亮樣式
      target.classList.add('highlighted-post');
    }
  }
});
</script>

<style>
.highlighted-post {
  border: 2px solidrgb(140, 174, 213) !important;
  background-color: #fff8e1 !important;
  transition: all 0.5s ease;
}

.role {
  font-style: italic;
  color:rgb(42, 120, 126); /* 橘色 */
  font-size: 0.85em;
  
  border-radius: 4px;
  padding: 2px 4px;
  background-color:rgb(122, 201, 221); /* 淡橘背景 */
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
      font-size: 1.5em; /* 放大 50% */
      color: #212529;
    }
    .comment-item p {
      font-size: 1.35em; /* 放大 50% */
      color: #212529;
    }
    .post-item h3 {
      font-size: 3em; /* 放大 100% */
    }
    .floating-btn {
      margin-top: 15px;
      display: inline-block;
      background: #007bff; /* 純藍色背景 */
      color: #fff;
      border: none;
      border-radius: 20px;
      padding: 10px 20px;
      font-size: 16px;
      transition: all 0.3s ease;
    }
    .floating-btn:hover {
      background: #0056b3; /* 深藍色背景 */
      transform: scale(1.05);
    }
    .floating-btn i {
      margin-left: 5px;
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
                  <?php if (strlen($content) > 300): ?>
                      <p class="short-content">
                          <?= nl2br(substr($content, 0, 300)) ?>...
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
                      <div id="top-comments-<?= $post['Post_ID'] ?>">
                          <?php foreach ($topComments as $comment): ?>
                              <div class="comment-item">
                                  <p><strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                                  <div class="meta">留言時間: <?= $comment['Comment_Time'] ?></div>
                                  <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                                      <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                                  </button>
                              </div>
                          <?php endforeach; ?>
                      </div>

                      <?php if (count($comments) > 3): ?>
                          <button id="show-more-comments" class="btn btn-link" onclick="showMoreComments(this, <?= $post['Post_ID'] ?>)">顯示更多留言</button>
                          <div id="all-comments-<?= $post['Post_ID'] ?>" style="display: none;">
                              <?php foreach (array_slice($comments, 3) as $comment): ?>
                                  <div class="comment-item">
                                      <p><strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                                      <div class="meta">留言時間: <?= $comment['Comment_Time'] ?></div>
                                      <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                                          <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                                      </button>
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
                <div class="post-item data-post-id="<?= $post['Post_ID'] ?>" id="post-<?= $post['Post_ID'] ?>">
                  <h3><?= htmlspecialchars($post['Title']) ?></h3>
                  <div class="meta">
                    <span>由 <?= htmlspecialchars($post['Nickname']) ?> <span class="role"><?= htmlspecialchars($post['Roles']) ?></span> 發布於 <?= $post['Post_Time'] ?></span>
                  </div>
                  <?php
                  $content = htmlspecialchars($post['Content']);
                  if (strlen($content) > 100): ?>
                    <p class="short-content">
                      <?= nl2br(substr($content, 0, 300)) ?>...
                      <a href="#" class="read-more" onclick="showFullContent(this, '<?= addslashes($content) ?>'); return false;">(查看更多)</a>
                    </p>
                  <?php else: ?>
                    <p><?= nl2br($content) ?></p>
                  <?php endif; ?>
                  <?php $alreadyLiked = in_array($post['Post_ID'], $likedPostIds); ?>
                  
                  
                  <button 
  class="btn-like <?= $alreadyLiked ? 'liked' : '' ?>" 
  data-post-id="<?= $post['Post_ID'] ?>" 
  <?= $alreadyLiked ? 'disabled' : '' ?>
>
  <i class="bi bi-heart"></i> <span><?= $post['Likes'] ?></span>
</button>

                  <!-- 顯示留言 -->
                  <div class="comments">
                    <?php
                    $commentsQuery = $conn->query("SELECT c.*, a.Nickname, a.Roles FROM comments c JOIN account a ON c.User_ID = a.User_ID WHERE c.Post_ID = " . $post['Post_ID'] . " ORDER BY c.Likes DESC, c.Comment_Time ASC");
                    $comments = [];
                    while ($comment = $commentsQuery->fetch_assoc()) {
                        $comments[] = $comment;
                    }
                    $topComments = array_slice($comments, 0, 3);
                    ?>

                    <div id="top-comments-<?= $post['Post_ID'] ?>">
                      <?php foreach ($topComments as $comment): ?>
                        <div class="comment-item">
                          <p><strong><?= htmlspecialchars($comment['Nickname']) ?> <span class="role"><?= htmlspecialchars($comment['Roles']) ?></span>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                          <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                          <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                            <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                          </button>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <?php if (count($comments) > 3): ?>
                      <button id="show-more-comments" class="btn btn-link" onclick="showMoreComments(this, <?= $post['Post_ID'] ?>)">顯示更多留言</button>
                      <div id="all-comments-<?= $post['Post_ID'] ?>" style="display: none;">
                        <?php foreach (array_slice($comments, 3) as $comment): ?>
                          <div class="comment-item">
                            <p><strong><?= htmlspecialchars($comment['Nickname']) ?> <span class="role"><?= htmlspecialchars($comment['Roles']) ?></span>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                            <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> </div>
                            <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                              <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                            </button>
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
                background: #007bff; /* 純藍色背景 */
                color: #fff;
                border: none;
                border-radius: 20px;
                padding: 10px 20px;
                font-size: 16px;
                transition: all 0.3s ease;
              }
              .floating-btn:hover {
                background: #0056b3; /* 深藍色背景 */
                transform: scale(1.05);
              }
              .floating-btn i {
                margin-left: 5px;
              }
            </style>

            

            <!-- Recent Posts and Comments Widget -->
            <div class="recent-posts-widget widget-item">
              
              <h4>近期文章</h4>
              <?php
              if (isset($_SESSION['user'])) {
                  $userEmail = $_SESSION['user'];
                  $recentPostsQuery = $conn->prepare("SELECT p.Title, p.Post_Time, p.Post_ID FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE a.`E-mail` = ? ORDER BY p.Post_Time DESC LIMIT 5");
                  $recentPostsQuery->bind_param("s", $userEmail);
                  $recentPostsQuery->execute();
                  $result = $recentPostsQuery->get_result();
                  if ($result->num_rows > 0): ?>
                    <?php while ($post = $result->fetch_assoc()): ?>
                      <div class="post-item">
                        <div>
                          <h5><a href="blog-details.php?highlight_id=<?= $post['Post_ID'] ?>"><?= htmlspecialchars($post['Title']) ?></a></h5>
                          <time datetime="<?= $post['Post_Time'] ?>"><?= $post['Post_Time'] ?></time>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>尚未發布任何文章。</p>
                  <?php endif;
              } else {
                  echo '<p>請先登入以查看您的文章。</p>';
              }
              ?>

              <h4>近期留言</h4>
              <?php
              if (isset($_SESSION['user'])) {
                  $userEmail = $_SESSION['user'];
                  $recentCommentsQuery = $conn->prepare("SELECT c.Content, c.Comment_Time, p.Title, p.Post_ID FROM comments c JOIN posts p ON c.Post_ID = p.Post_ID JOIN account a ON c.User_ID = a.User_ID WHERE a.`E-mail` = ? ORDER BY c.Comment_Time DESC LIMIT 5");
                  $recentCommentsQuery->bind_param("s", $userEmail);
                  $recentCommentsQuery->execute();
                  $result = $recentCommentsQuery->get_result();
                  if ($result->num_rows > 0): ?>
                    <?php while ($comment = $result->fetch_assoc()): ?>
                      <div class="post-item ">
                        <div>
                          <p>留言於文章: <strong><a href="blog-details.php?highlight_id=<?= $comment['Post_ID'] ?>"><?= htmlspecialchars($comment['Title']) ?></a></strong></p>
                          <time datetime="<?= $comment['Comment_Time'] ?>">留言時間: <?= $comment['Comment_Time'] ?></time>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>尚未發布任何留言。</p>
                  <?php endif;
              } else {
                  echo '<p>請先登入以查看您的留言。</p>';
              }
              ?>
            </div><!--/Recent Posts and Comments Widget -->

            <!-- Tags Widget -->
            <div class="tags-widget widget-item">

              <h3 class="widget-title">Tags</h3>
              <ul>
                <li><a href="#">App</a></li>
                <li><a href="#">IT</a></li>
                <li><a href="#">Business</a></li>
                <li><a href="#">Mac</a></li>
                <li><a href="#">Design</a></li>
                <li><a href="#">Office</a></li>
                <li><a href="#">Creative</a></li>
                <li><a href="#">Studio</a></li>
                <li><a href="#">Smart</a></li>
                <li><a href="#">Tips</a></li>
                <li><a href="#">Marketing</a></li>
              </ul>

            </div><!--/Tags Widget -->

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