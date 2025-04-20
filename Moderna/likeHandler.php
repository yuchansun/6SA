<?php
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['postId']) && !isset($input['commentId'])) {
        echo json_encode(['success' => false, 'message' => '缺少 postId 或 commentId']);
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

    if (isset($input['commentId']) && !empty($input['commentId'])) {
        $commentId = intval($input['commentId']);

        // Check if the user has already liked the comment
        $checkCommentLikeQuery = $conn->prepare("SELECT * FROM likes WHERE User_ID = ? AND Comment_ID = ?");
        $checkCommentLikeQuery->bind_param("ii", $userId, $commentId);
        $checkCommentLikeQuery->execute();
        $checkCommentLikeResult = $checkCommentLikeQuery->get_result();

        if ($checkCommentLikeResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => '你已經對此留言點過讚了']);
            exit;
        }

        // Insert the like for the comment
        $query = $conn->prepare("INSERT INTO likes (User_ID, Post_ID, Comment_ID, Like_Time) VALUES (?, NULL, ?, NOW())");
        $query->bind_param("ii", $userId, $commentId);

        if ($query->execute()) {
            $updateCommentLikesQuery = $conn->prepare("UPDATE comments SET Likes = Likes + 1 WHERE Comment_ID = ?");
            $updateCommentLikesQuery->bind_param("i", $commentId);
            $updateCommentLikesQuery->execute();

            $result = $conn->prepare("SELECT Likes FROM comments WHERE Comment_ID = ?");
            $result->bind_param("i", $commentId);
            $result->execute();
            $likesResult = $result->get_result();
            $comment = $likesResult->fetch_assoc();

            echo json_encode(['success' => true, 'commentId' => $commentId, 'newLikesCount' => $comment['Likes']]);
        } else {
            echo json_encode(['success' => false, 'message' => '無法完成對留言的點讚操作']);
        }
        exit;
    }

    if (isset($input['postId']) && !empty($input['postId'])) {
        $postId = intval($input['postId']);

        // Check if the user has already liked the post
        $checkLikeQuery = $conn->prepare("SELECT * FROM likes WHERE User_ID = ? AND Post_ID = ?");
        $checkLikeQuery->bind_param("ii", $userId, $postId);
        $checkLikeQuery->execute();
        $checkLikeResult = $checkLikeQuery->get_result();

        if ($checkLikeResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => '你已經點過讚了']);
            exit;
        }

        // Insert the like for the post
        $query = $conn->prepare("INSERT INTO likes (User_ID, Post_ID, Comment_ID, Like_Time) VALUES (?, ?, NULL, NOW())");
        $query->bind_param("ii", $userId, $postId);

        if ($query->execute()) {
            $updateLikesQuery = $conn->prepare("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = ?");
            $updateLikesQuery->bind_param("i", $postId);
            $updateLikesQuery->execute();

            $result = $conn->prepare("SELECT Likes FROM posts WHERE Post_ID = ?");
            $result->bind_param("i", $postId);
            $result->execute();
            $likesResult = $result->get_result();
            $post = $likesResult->fetch_assoc();

            echo json_encode(['success' => true, 'newLikesCount' => $post['Likes']]);
        } else {
            echo json_encode(['success' => false, 'message' => '無法完成點讚操作']);
        }
        exit;
    }
}
?>