<?php
include('db.php'); // 假設這是你的資料庫連接檔案

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['postId']) || empty($input['postId'])) {
        echo json_encode(['success' => false, 'message' => '缺少 postId']);
        exit;
    }

    session_start();
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => '用戶未登入']);
        exit;
    }

    $userEmail = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT User_ID FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => '無法找到使用者 ID']);
        exit;
    }

    $user = $result->fetch_assoc();
    $userId = $user['User_ID'];
    $postId = intval($input['postId']);

    // Check if the user has already liked the post
    $checkLikeQuery = $conn->prepare("SELECT * FROM likes WHERE User_ID = ? AND Post_ID = ?");
    $checkLikeQuery->bind_param("ii", $userId, $postId);
    $checkLikeQuery->execute();
    $checkLikeResult = $checkLikeQuery->get_result();

    if ($checkLikeResult->num_rows > 0) {
        // Unlike the post
        $deleteLikeQuery = $conn->prepare("DELETE FROM likes WHERE User_ID = ? AND Post_ID = ?");
        $deleteLikeQuery->bind_param("ii", $userId, $postId);
        $deleteLikeQuery->execute();

        $updatePostLikesQuery = $conn->prepare("UPDATE posts SET Likes = Likes - 1 WHERE Post_ID = ?");
        $updatePostLikesQuery->bind_param("i", $postId);
        $updatePostLikesQuery->execute();

        $result = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
        $result->bind_param("i", $postId);
        $result->execute();
        $likesResult = $result->get_result();
        $post = $likesResult->fetch_assoc();

        echo json_encode(['success' => true, 'newLikesCount' => $post['Likes'], 'liked' => false]);
        exit;
    }

    // Like the post
    $query = $conn->prepare("INSERT INTO likes (User_ID, Post_ID, Comment_ID, Like_Time) VALUES (?, ?, NULL, NOW())");
    $query->bind_param("ii", $userId, $postId);

    if ($query->execute()) {
        $updatePostLikesQuery = $conn->prepare("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = ?");
        $updatePostLikesQuery->bind_param("i", $postId);
        $updatePostLikesQuery->execute();

        $result = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
        $result->bind_param("i", $postId);
        $result->execute();
        $likesResult = $result->get_result();
        $post = $likesResult->fetch_assoc();

        echo json_encode(['success' => true, 'newLikesCount' => $post['Likes'], 'liked' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '無法完成對貼文的點讚操作']);
    }
    exit;
}
?>
