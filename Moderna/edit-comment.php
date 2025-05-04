<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = $_POST['comment_id'];
    $commentContent = trim($_POST['content']); // 確保內容被正確接收

    if (!empty($commentContent)) {
        $updateComment = $conn->prepare("UPDATE comments SET Content = ? WHERE Comment_ID = ?");
        $updateComment->bind_param("si", $commentContent, $commentId);

        if ($updateComment->execute()) {
            // 獲取該留言所屬的文章 ID
            $postIdQuery = $conn->prepare("SELECT Post_ID FROM comments WHERE Comment_ID = ?");
            $postIdQuery->bind_param("i", $commentId);
            $postIdQuery->execute();
            $postIdResult = $postIdQuery->get_result();
            if ($postIdResult->num_rows > 0) {
                $postId = $postIdResult->fetch_assoc()['Post_ID'];

                // 計算該文章所在的分頁
                $postsPerPage = 5; // 確保與分頁邏輯一致
                $positionResult = $conn->query("SELECT COUNT(*) AS position FROM posts WHERE Post_Time > (SELECT Post_Time FROM posts WHERE Post_ID = $postId)");
                $position = $positionResult->fetch_assoc()['position'];
                $page = floor($position / $postsPerPage) + 1;

                // 跳轉到該文章並高亮顯示
                header("Location: blog-details.php?page=$page&highlight_id=$postId");
                exit;
            }
        } else {
            error_log("Failed to update comment.");
        }
    } else {
        error_log("Comment content is empty.");
    }
}
?>