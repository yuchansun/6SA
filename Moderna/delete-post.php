<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = intval($_POST['post_id']);

    // 檢查是否存在該文章
    $checkPost = $conn->prepare("SELECT Post_ID FROM posts WHERE Post_ID = ? AND is_deleted = 0");
    $checkPost->bind_param("i", $postId);
    $checkPost->execute();
    $result = $checkPost->get_result();

    if ($result->num_rows > 0) {
        // 執行軟刪除操作
        $deletePost = $conn->prepare("UPDATE posts SET is_deleted = 1 WHERE Post_ID = ?");
        $deletePost->bind_param("i", $postId);

        if ($deletePost->execute()) {
            // 重定向回 blog-details.php
            header("Location: blog-details.php");
            exit;
        } else {
            echo "刪除失敗，請稍後再試。";
        }
    } else {
        echo "該文章不存在或已被刪除。";
    }
} else {
    echo "無效的請求，請確認提交的資料是否正確。";
}
?>