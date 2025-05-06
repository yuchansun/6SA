<?php
session_start();
require_once 'db.php'; // Your DB connection file

if (!isset($_SESSION['User_ID']) || !isset($_POST['comment_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['User_ID'];
$comment_id = intval($_POST['comment_id']);

// Get comment info
$stmt = $conn->prepare("SELECT User_ID FROM comments WHERE Comment_ID = ? AND is_deleted = 0");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    exit("留言不存在或已刪除");
}
$comment = $result->fetch_assoc();
$comment_owner_id = $comment['User_ID'];

// Get user role
$stmt = $conn->prepare("SELECT Roles FROM account WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role_result = $stmt->get_result();
$role = $role_result->fetch_assoc()['Roles'] ?? '';

// Only allow delete if current user is the comment owner OR is 管理者
if ($user_id != $comment_owner_id && $role != '管理者') {
    exit("您無權刪除這則留言");
}

// Soft delete: update `is_deleted` flag
$stmt = $conn->prepare("UPDATE comments SET is_deleted = 1 WHERE Comment_ID = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
