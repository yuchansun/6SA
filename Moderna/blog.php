<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Blog - 貼文功能</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: Arial, sans-serif;
    }

    .container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }

    .card {
      margin-bottom: 20px;
      border: none;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title {
      font-size: 1.25rem;
      font-weight: bold;
    }

    .btn {
      border-radius: 20px;
    }

    .btn-primary {
      background-color: #1877f2;
      border: none;
    }

    .btn-primary:hover {
      background-color: #145dbf;
    }

    .card-footer {
      background-color: #f8f9fa;
    }

    .input-group input {
      border-radius: 20px;
    }

    .input-group button {
      border-radius: 20px;
    }

    .like-btn {
      color: #1877f2;
      cursor: pointer;
    }

    .like-btn:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
<main class="main">
  <div class="page-title dark-background">
    <div class="container position-relative"style="background-color: rgb(30, 67, 86);">
      <h1>討論區</h1>
      <p>在這裡分享您的想法，並與其他人互動。</p>
    </div>
  </div>


  <div class="container mt-5">
    <h2 class="text-center">貼文功能</h2>

    <!-- 發布貼文表單 -->
    <form method="POST" action="" class="mb-4">
      <div class="mb-3">
        <label for="title" class="form-label">標題</label>
        <input type="text" class="form-control" id="title" name="title" required>
      </div>
      <div class="mb-3">
        <label for="content" class="form-label">內容</label>
        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">發布貼文</button>
    </form>

    <?php
    $conn = new mysqli("localhost", "root", "", "sa-6");
    if ($conn->connect_error) {
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    // 發布貼文
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $userId = 1; // 假設使用者 ID 為 1
        $conn->query("INSERT INTO posts (Title, Content, User_ID) VALUES ('$title', '$content', $userId)");
    }

    // 顯示貼文
    $posts = $conn->query("SELECT p.*, a.Nickname FROM posts p JOIN account a ON p.User_ID = a.User_ID ORDER BY Post_Time DESC");
    while ($post = $posts->fetch_assoc()): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($post['Title']) ?></h5>
          <h6 class="card-subtitle mb-2 text-muted">由 <?= htmlspecialchars($post['Nickname']) ?> 發布於 <?= $post['Post_Time'] ?></h6>
          <p class="card-text"><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
          <button class="btn btn-outline-primary btn-sm" onclick="likePost(<?= $post['Post_ID'] ?>, this)">讚 (<?= $post['Likes'] ?>)</button>
        </div>

        <!-- 留言區 -->
        <div class="card-footer">
          <form method="POST" action="">
            <input type="hidden" name="post_id" value="<?= $post['Post_ID'] ?>">
            <div class="input-group">
              <input type="text" class="form-control" name="comment" placeholder="新增留言..." required>
              <button class="btn btn-primary" type="submit">送出</button>
            </div>
          </form>

          <?php
          $comments = $conn->query("SELECT c.*, a.Nickname FROM comments c JOIN account a ON c.User_ID = a.User_ID WHERE c.Post_ID = " . $post['Post_ID'] . " ORDER BY Comment_Time ASC");
          while ($comment = $comments->fetch_assoc()): ?>
            <div class="mt-2">
              <strong><?= htmlspecialchars($comment['Nickname']) ?>:</strong>
              <?= nl2br(htmlspecialchars($comment['Content'])) ?>
              <button class="btn btn-outline-primary btn-sm" onclick="likeComment(<?= $comment['Comment_ID'] ?>, this)">讚 (<?= $comment['Likes'] ?>)</button>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    <?php endwhile; ?>

    <?php $conn->close(); ?>
  </div>
</main>

<script>
function likePost(postId, element) {
  fetch(`like.php?type=post&id=${postId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        element.textContent = `讚 (${data.likes})`;
      }
    });
}

function likeComment(commentId, element) {
  fetch(`like.php?type=comment&id=${commentId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        element.textContent = `讚 (${data.likes})`;
      }
    });
}
</script>

</body>

</html>