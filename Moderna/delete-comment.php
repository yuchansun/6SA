<?php
require_once 'db.php'; // 確保引用正確的資料庫連線檔案

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $commentId = intval($_POST['comment_id']);

    // 檢查是否存在該留言
    $checkComment = $conn->prepare("SELECT Comment_ID FROM comments WHERE Comment_ID = ? AND is_deleted = 0");
    $checkComment->bind_param("i", $commentId);
    $checkComment->execute();
    $result = $checkComment->get_result();

    if ($result->num_rows > 0) {
        // 執行軟刪除操作
        $deleteComment = $conn->prepare("UPDATE comments SET is_deleted = 1 WHERE Comment_ID = ?");
        $deleteComment->bind_param("i", $commentId);

        if ($deleteComment->execute()) {
            // 重定向回 blog-details.php
            header("Location: blog-details.php");
            exit;
        } else {
            echo "刪除失敗，請稍後再試。";
        }
    } else {
        echo "該留言不存在或已被刪除。";
    }
} else {
    echo "無效的請求，請確認提交的資料是否正確。";
}
?>