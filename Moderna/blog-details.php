<?php include('header.php'); ?>
<?php
// 連接資料庫
require_once 'db.php';

// 處理新增貼文提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['comment'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['comment']);
    $userId = 1; // 假設用戶 ID 為 1，應根據實際情況動態獲取

    $insertPost = $conn->prepare("INSERT INTO posts (Title, Content, User_ID, Post_Time) VALUES (?, ?, ?, NOW())");
    $insertPost->bind_param("ssi", $title, $content, $userId);
    $insertPost->execute();
    header("Location: blog-details.php");
    exit;
}

// 處理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['comment'])) {
    $postId = intval($_POST['post_id']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $userId = 1; // 假設用戶 ID 為 1，應根據實際情況動態獲取

    $insertComment = $conn->prepare("INSERT INTO comments (Post_ID, Content, User_ID, Comment_Time) VALUES (?, ?, ?, NOW())");
    $insertComment->bind_param("isi", $postId, $comment, $userId);
    $insertComment->execute();
    header("Location: blog-details.php");
    exit;
}

// 獲取所有貼文
$postsQuery = $conn->query("SELECT p.*, a.Nickname FROM posts p JOIN account a ON p.User_ID = a.User_ID ORDER BY Post_Time DESC");

// 搜尋功能
$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $searchQuery = $conn->query("SELECT p.*, a.Nickname FROM posts p JOIN account a ON p.User_ID = a.User_ID WHERE p.Title LIKE '%$searchTerm%' ORDER BY Post_Time DESC");
    while ($row = $searchQuery->fetch_assoc()) {
        $searchResults[] = $row;
    }
}
?>
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
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

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

          <!-- 顯示貼文 -->
          <section id="blog-posts" class="blog-posts section">
            <div class="container">
              <?php while ($post = $postsQuery->fetch_assoc()): ?>
                <div class="post-item">
                  <h3><?= htmlspecialchars($post['Title']) ?></h3>
                  <div class="meta">
                    <span>由 <?= htmlspecialchars($post['Nickname']) ?> 發布於 <?= $post['Post_Time'] ?></span>
                  </div>
                  <p><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
                  <button class="btn-like" onclick="likePost(<?= $post['Post_ID'] ?>, this)">
                    <i class="bi bi-heart"></i> <span><?= $post['Likes'] ?></span>
                  </button>

                  <!-- 顯示留言 -->
                  <div class="comments">
                    <?php
                    $commentsQuery = $conn->query("SELECT c.*, a.Nickname FROM comments c JOIN account a ON c.User_ID = a.User_ID WHERE c.Post_ID = " . $post['Post_ID'] . " ORDER BY Comment_Time ASC");
                    while ($comment = $commentsQuery->fetch_assoc()): ?>
                      <div class="comment-item">
                        <p><strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['Content'])) ?></p>
                        <div class="meta">留言時間: <?= $comment['Comment_Time'] ?></div>
                        <button class="btn-like" onclick="likeComment(<?= $comment['Comment_ID'] ?>, this)">
                          <i class="bi bi-heart"></i> <span><?= $comment['Likes'] ?></span>
                        </button>
                      </div>
                    <?php endwhile; ?>
                  </div>

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
            </div>
          </section>

          <script>
          function likePost(postId, button) {
            // AJAX 請求來更新文章的點讚數
            fetch(`like_post.php?post_id=${postId}`)
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  button.querySelector('span').textContent = data.likes;
                }
              });
          }

          function likeComment(commentId, button) {
            // AJAX 請求來更新留言的點讚數
            fetch(`like_comment.php?comment_id=${commentId}`)
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  button.querySelector('span').textContent = data.likes;
                }
              });
          }
          </script>

        </div>

        <div class="col-lg-4 sidebar">

          <div class="widgets-container">

            <!-- Blog Author Widget -->
            <div class="blog-author-widget widget-item">

              <div class="d-flex flex-column align-items-center">
                <img src="assets/img/blog/blog-author.jpg" class="rounded-circle flex-shrink-0" alt="">
                <h4>登入者匿名或名稱</h4>
                

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

            

            <!-- Recent Posts Widget -->
            <div class="recent-posts-widget widget-item">

              <h3 class="widget-title">近期紀錄</h3>

              <div class="post-item">
                <img src="assets/img/blog/blog-recent-1.jpg" alt="" class="flex-shrink-0">
                <div>
                  <h4><a href="blog-details.html">Nihil blanditiis at in nihil autem</a></h4>
                  <time datetime="2020-01-01">Jan 1, 2020</time>
                </div>
              </div><!-- End recent post item-->

              <div class="post-item">
                <img src="assets/img/blog/blog-recent-2.jpg" alt="" class="flex-shrink-0">
                <div>
                  <h4><a href="blog-details.html">Quidem autem et impedit</a></h4>
                  <time datetime="2020-01-01">Jan 1, 2020</time>
                </div>
              </div><!-- End recent post item-->

              <div class="post-item">
                <img src="assets/img/blog/blog-recent-3.jpg" alt="" class="flex-shrink-0">
                <div>
                  <h4><a href="blog-details.html">Id quia et et ut maxime similique occaecati ut</a></h4>
                  <time datetime="2020-01-01">Jan 1, 2020</time>
                </div>
              </div><!-- End recent post item-->

              <div class="post-item">
                <img src="assets/img/blog/blog-recent-4.jpg" alt="" class="flex-shrink-0">
                <div>
                  <h4><a href="blog-details.html">Laborum corporis quo dara net para</a></h4>
                  <time datetime="2020-01-01">Jan 1, 2020</time>
                </div>
              </div><!-- End recent post item-->

              <div class="post-item">
                <img src="assets/img/blog/blog-recent-5.jpg" alt="" class="flex-shrink-0">
                <div>
                  <h4><a href="blog-details.html">Et dolores corrupti quae illo quod dolor</a></h4>
                  <time datetime="2020-01-01">Jan 1, 2020</time>
                </div>
              </div><!-- End recent post item-->

            </div><!--/Recent Posts Widget -->

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