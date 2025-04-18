<?php
// update_todo_status.php
header('Content-Type: application/json');
include('db.php');

// 取得 JSON 請求內容
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? '';
$todoId = $data['todoId'] ?? '';
$isDone = $data['isDone'] ?? 0;

if (!$userId || !$todoId) {
    echo json_encode(['error' => '缺少必要資料']);
    exit;
}

// 更新 user_todos 表的 is_done 狀態
$sql = "
    UPDATE user_todos
    SET is_done = ?, updated_at = NOW()
    WHERE user_id = ? AND todo_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $isDone, $userId, $todoId);
$stmt->execute();

echo json_encode(['status' => 'success']);
$stmt->close();
$conn->close();
