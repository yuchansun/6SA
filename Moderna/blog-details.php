<?php include('header.php'); ?>


<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: contact.php?error=進入討論區需要先登入喔");
    exit();
}

// 連接資料庫
require_once 'db.php';

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
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['comment']);

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
    $comment = $conn->real_escape_string($_POST['comment']);

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
            header("Location: blog-details.php");
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

$postsQuery = $conn->prepare("SELECT p.*, a.Nickname FROM posts p JOIN account a ON p.User_ID = a.User_ID ORDER BY Post_Time DESC LIMIT ? OFFSET ?");
$postsQuery->bind_param("ii", $postsPerPage, $offset);
$postsQuery->execute();
$postsResult = $postsQuery->get_result();

// 搜尋功能
$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = $conn->query("SELECT p.*, a.Nickname FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE p.Title LIKE '%$searchTerm%' ORDER BY Post_Time DESC");
    while ($row = $searchQuery->fetch_assoc()) {
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
function handleLike(button, type, id) {
    const payload = type === 'post' ? { postId: id } : { commentId: id };

    fetch(type === 'post' ? 'likePost.php' : 'likeComment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`伺服器回應錯誤，狀態碼: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            const icon = button.querySelector('i');
            icon.classList.toggle('bi-heart', !data.liked);
            icon.classList.toggle('bi-heart-fill', data.liked);
            button.querySelector('span').textContent = data.newLikesCount;
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`發生錯誤: ${error.message}`);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-like').forEach(function(button) {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentId = this.getAttribute('data-comment-id');

            if (postId) {
                handleLike(this, 'post', postId);
            } else if (commentId) {
                handleLike(this, 'comment', commentId);
            } else {
                alert('缺少 postId 或 commentId');
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
            <section id="search-results" class="search-results section">
              <div class="container">
                <h3>搜尋結果：</h3>
                <?php foreach ($searchResults as $post): ?>
                  <div class="post-item">
                    <h3><?= htmlspecialchars($post['Title']) ?></h3>
                    <div class="meta">
                      <span>由 <?= htmlspecialchars($post['Nickname']) ?> 發布於 <?= $post['Post_Time'] ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
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
                <div class="post-item data-post-id="<?= $post['Post_ID'] ?>" id="post-<?= $post['Post_ID'] ?>"">
                  <h3><?= htmlspecialchars($post['Title']) ?></h3>
                  <div class="meta">
                    <span>由 <?= htmlspecialchars($post['Nickname']) ?> 發布於 <?= $post['Post_Time'] ?></span>
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
                    $commentsQuery = $conn->query("SELECT c.*, a.Nickname FROM comments c JOIN account a ON c.User_ID = a.User_ID WHERE c.Post_ID = " . $post['Post_ID'] . " ORDER BY c.Likes DESC, c.Comment_Time ASC");
                    $comments = [];
                    while ($comment = $commentsQuery->fetch_assoc()) {
                        $comments[] = $comment;
                    }
                    $topComments = array_slice($comments, 0, 3);
                    ?>

                    <div id="top-comments">
                      <?php foreach ($topComments as $comment): ?>
                        <div class="comment-item">
                          <p><strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                          <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> | 點讚數: <?= $comment['Likes'] ?></div>
                          <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                            <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                          </button>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <?php if (count($comments) > 3): ?>
                      <button id="show-more-comments" class="btn btn-link">顯示更多留言</button>
                      <div id="all-comments" style="display: none;">
                        <?php foreach (array_slice($comments, 3) as $comment): ?>
                          <div class="comment-item">
                            <p><strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                            <div class="meta">留言時間: <?= $comment['Comment_Time'] ?> | 點讚數: <?= $comment['Likes'] ?></div>
                            <button class="btn-like" data-comment-id="<?= $comment['Comment_ID'] ?>">
                              <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                            </button>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <script>
                  document.getElementById('show-more-comments')?.addEventListener('click', function() {
                    document.getElementById('all-comments').style.display = 'block';
                    this.style.display = 'none';
                  });
                  </script>

                  <!-- 新增留言表單 -->
                  <form method="POST" action="">
                    <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
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

          <script>
          function showFullContent(link, fullContent) {
            const parent = link.closest('.short-content');
            parent.innerHTML = fullContent;
          }
          </script>

        </div>

        <div class="col-lg-4 sidebar">

          <div class="widgets-container">

            <!-- Blog Author Widget -->
            <div class="blog-author-widget widget-item">

              <div class="d-flex flex-column align-items-center">
                <img src="assets/img/blog/blog-author.jpg" class="rounded-circle flex-shrink-0" alt="">
                <h4><?= htmlspecialchars($nickname) ?></h4>
                

                <p>
                  登入者介紹 看需不需要
                </p>

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
                <input type="text" name="search" placeholder="搜尋文章標題..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
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
                background: linear-gradient(45deg, #007bff, #00d4ff);
                color: #fff;
                border: none;
                border-radius: 20px;
                padding: 10px 20px;
                font-size: 16px;
                transition: all 0.3s ease;
              }
              .floating-btn:hover {
                background: linear-gradient(45deg, #0056b3, #0099cc);
                transform: scale(1.05);
              }
              .floating-btn i {
                margin-left: 5px;
              }
            </style>

            

            <!-- Recent Posts and Comments Widget -->
            <div class="recent-posts-widget widget-item">
              <h3 class="widget-title">近期紀錄</h3>
              <h4>近期文章</h4>
              <?php
              if (isset($_SESSION['user'])) {
                  $userEmail = $_SESSION['user'];
                  $recentPostsQuery = $conn->prepare("SELECT p.Title, p.Post_Time FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE a.`E-mail` = ? ORDER BY p.Post_Time DESC LIMIT 5");
                  $recentPostsQuery->bind_param("s", $userEmail);
                  $recentPostsQuery->execute();
                  $result = $recentPostsQuery->get_result();
                  if ($result->num_rows > 0): ?>
                    <?php while ($post = $result->fetch_assoc()): ?>
                      <div class="post-item">
                        <div>
                          <h5><?= htmlspecialchars($post['Title']) ?></h5>
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
                  $recentCommentsQuery = $conn->prepare("SELECT c.Content, c.Comment_Time, p.Title FROM comments c JOIN posts p ON c.Post_ID = p.Post_ID JOIN account a ON c.User_ID = a.User_ID WHERE a.`E-mail` = ? ORDER BY c.Comment_Time DESC LIMIT 5");
                  $recentCommentsQuery->bind_param("s", $userEmail);
                  $recentCommentsQuery->execute();
                  $result = $recentCommentsQuery->get_result();
                  if ($result->num_rows > 0): ?>
                    <?php while ($comment = $result->fetch_assoc()): ?>
                      <div class="post-item ">
                        <div>
                          <p>留言於文章: <strong><?= htmlspecialchars($comment['Title']) ?></strong></p>
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

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Moderna</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

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