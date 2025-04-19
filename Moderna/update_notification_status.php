<?php
$mysqli = new mysqli('localhost', 'root', '', 'sa-6');
if ($mysqli->connect_error) {
    die('資料庫連線失敗: ' . $mysqli->connect_error);
}

// 接收 JSON
$data = json_decode(file_get_contents('php://input'), true);

// 檢查值是否有拿到
if (!isset($data['userId'], $data['todoId'], $data['isNotified'])) {
    echo json_encode(['success' => false, 'message' => '缺少參數']);
    exit;
}

$userId = intval($data['userId']);
$todoId = intval($data['todoId']);
$isNotified = $data['isNotified'] ? 1 : 0;

// 更新資料庫
$stmt = $mysqli->prepare("UPDATE user_todos SET is_notified = ?, updated_at = NOW() WHERE user_id = ? AND todo_id = ?");
$stmt->bind_param('iii', $isNotified, $userId, $todoId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '通知狀態已更新']);
} else {
    echo json_encode(['success' => false, 'message' => '更新失敗: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
