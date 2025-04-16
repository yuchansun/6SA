<?php
require_once 'db.php';

if (isset($_GET['comment_id'])) {
    $commentId = intval($_GET['comment_id']);

    // 更新點讚數量
    $conn->query("UPDATE comments SET Likes = Likes + 1 WHERE Comment_ID = $commentId");

    // 獲取最新的點讚數量
    $result = $conn->query("SELECT Likes FROM comments WHERE Comment_ID = $commentId");
    $likes = $result->fetch_assoc()['Likes'];

    echo json_encode(['success' => true, 'likes' => $likes]);
} else {
    echo json_encode(['success' => false]);
}
?>