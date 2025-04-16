<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "sa-6");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit;
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($type === 'post') {
    $conn->query("UPDATE posts SET Likes = Likes + 1 WHERE Post_ID = $id");
    $result = $conn->query("SELECT Likes FROM posts WHERE Post_ID = $id");
    $likes = $result->fetch_assoc()['Likes'] ?? 0;
    echo json_encode(["success" => true, "likes" => $likes]);
} elseif ($type === 'comment') {
    $conn->query("UPDATE comments SET Likes = Likes + 1 WHERE Comment_ID = $id");
    $result = $conn->query("SELECT Likes FROM comments WHERE Comment_ID = $id");
    $likes = $result->fetch_assoc()['Likes'] ?? 0;
    echo json_encode(["success" => true, "likes" => $likes]);
} else {
    echo json_encode(["success" => false, "message" => "無效的請求"]);
}

$conn->close();
?>