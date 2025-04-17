<?php
header('Content-Type: application/json');
include('db.php');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? '';
$todoId = $data['todoId'] ?? '';
$isDone = $data['isDone'] ?? 0;

if (!$userId || !$todoId) {
    echo json_encode(['error' => '缺少必要資料']);
    exit;
}

// 如果已經有紀錄就更新，否則就新增
$sql = "
    INSERT INTO user_todos (user_id, todo_id, is_done)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE is_done = VALUES(is_done)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssi', $userId, $todoId, $isDone);
$success = $stmt->execute();

echo json_encode(['success' => $success]);

$stmt->close();
$conn->close();
