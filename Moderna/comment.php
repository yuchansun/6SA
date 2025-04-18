<?php
session_start();
require_once 'db_connection.php';

if (isset($_POST['submit_comment']) && isset($_SESSION['user'])) {
    $userEmail = $_SESSION['user'];
    $content = trim($_POST['comment_content']);
    $postId = intval($_POST['post_id']);

    if (empty($content)) {
        echo "留言內容不能為空。";
        exit;
    }

    // 確認使用者 ID
    $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
    if (!$stmt) {
        echo "資料庫錯誤：" . $conn->error;
        exit;
    }
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['User_ID'];

        // 插入留言
        $stmt = $conn->prepare("INSERT INTO comments (User_ID, Post_ID, Content, Comment_Time) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            echo "資料庫錯誤：" . $conn->error;
            exit;
        }
        $stmt->bind_param("iis", $userId, $postId, $content);
        if ($stmt->execute()) {
            echo "留言成功！";
        } else {
            echo "留言失敗，請稍後再試。";
        }
        $stmt->close();
    } else {
        echo "無法找到對應的使用者，請重新登入。";
    }
} else {
    echo "請先登入後再發布留言。";
}
?>