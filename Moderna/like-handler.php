<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

$userEmail = $_SESSION['user'];
$stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => '無法找到使用者']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['User_ID'];

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['postId']) ? intval($data['postId']) : null;
$commentId = isset($data['commentId']) ? intval($data['commentId']) : null;

if (!$postId && !$commentId) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

// 檢查是否已經點讚過
$checkLike = $conn->prepare("SELECT * FROM likes WHERE User_ID = ? AND (Post_ID = ? OR Comment_ID = ?)");
$checkLike->bind_param("iii", $userId, $postId, $commentId);
$checkLike->execute();
$likeResult = $checkLike->get_result();

if ($likeResult->num_rows > 0) {
    // 如果已點讚，執行取消點讚
    $deleteLike = $conn->prepare("DELETE FROM likes WHERE User_ID = ? AND (Post_ID = ? OR Comment_ID = ?)");
    $deleteLike->bind_param("iii", $userId, $postId, $commentId);
    if ($deleteLike->execute()) {
        if ($postId) {
            // 更新文章的點讚數
            $updateLikes = $conn->prepare("UPDATE posts SET Likes = Likes - 1 WHERE Post_ID = ?");
            $updateLikes->bind_param("i", $postId);
            $updateLikes->execute();

            // 獲取最新的點讚數
            $stmt = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
            $stmt->bind_param("i", $postId);
        } else {
            // 更新留言的點讚數
            $updateLikes = $conn->prepare("UPDATE comments SET Likes = Likes - 1 WHERE Comment_ID = ?");
            $updateLikes->bind_param("i", $commentId);
            $updateLikes->execute();

            // 獲取最新的點讚數
            $stmt = $conn->prepare("SELECT Likes FROM comments WHERE Comment_ID = ?");
            $stmt->bind_param("i", $commentId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $likes = $result->fetch_assoc()['Likes'];

        echo json_encode(['success' => true, 'likes' => $likes, 'action' => 'unliked']);
    } else {
        echo json_encode(['success' => false, 'message' => '取消點讚失敗']);
    }
} else {
    // 插入點讚記錄
    $addLike = $conn->prepare("INSERT INTO likes (User_ID, Post_ID, Comment_ID, Like_Time) VALUES (?, ?, ?, NOW())");
    $addLike->bind_param("iii", $userId, $postId, $commentId);

    if ($addLike->execute()) {
        if ($postId) {
            // 更新文章的點讚數
            $updateLikes = $conn->prepare("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = ?");
            $updateLikes->bind_param("i", $postId);
            $updateLikes->execute();

            // 獲取最新的點讚數
            $stmt = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
            $stmt->bind_param("i", $postId);
        } else {
            // 更新留言的點讚數
            $updateLikes = $conn->prepare("UPDATE comments SET Likes = Likes + 1 WHERE Comment_ID = ?");
            $updateLikes->bind_param("i", $commentId);
            $updateLikes->execute();

            // 獲取最新的點讚數
            $stmt = $conn->prepare("SELECT Likes FROM comments WHERE Comment_ID = ?");
            $stmt->bind_param("i", $commentId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $likes = $result->fetch_assoc()['Likes'];

        echo json_encode(['success' => true, 'likes' => $likes, 'action' => 'liked']);
    } else {
        echo json_encode(['success' => false, 'message' => '點讚失敗']);
    }
}
?>
