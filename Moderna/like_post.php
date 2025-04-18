<?php
require_once 'db.php';

if (isset($_GET['post_id'])) {
    $postId = intval($_GET['post_id']);

    // 更新點讚數量
    $conn->query("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = $postId");

    // 獲取最新的點讚數量
    $result = $conn->query("SELECT Likes FROM posts WHERE Post_ID = $postId");
    $likes = $result->fetch_assoc()['Likes'];

    echo json_encode(['success' => true, 'likes' => $likes]);
} else {
    echo json_encode(['success' => false]);
}
?>