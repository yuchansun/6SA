<?php
$mysqli = new mysqli('localhost', 'root', '', 'sa-6');

if ($mysqli->connect_error) {
    die('資料庫連線失敗: ' . $mysqli->connect_error);
}

// 接收資料
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'];
$todoId = $data['todoId'];
$isDone = $data['isDone'];

// 更新
$stmt = $mysqli->prepare("UPDATE user_todos SET is_done = ?, updated_at = NOW() WHERE user_id = ? AND todo_id = ?");
$stmt->bind_param('iii', $isDone, $userId, $todoId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '更新成功']);
} else {
    echo json_encode(['error' => '更新失敗: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
