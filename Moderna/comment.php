<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "sa-6");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = intval($_POST['post_id'] ?? 0);
    $userId = 1; // 假設使用者 ID 為 1
    $content = $conn->real_escape_string($_POST['comment'] ?? '');

    if ($postId > 0 && !empty($content)) {
        $conn->query("INSERT INTO comments (Post_ID, User_ID, Content) VALUES ($postId, $userId, '$content')");
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "無效的留言內容"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "無效的請求"]);
}

$conn->close();
?>