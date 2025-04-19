<?php
header('Content-Type: application/json');
require_once("db.php");

// 解析 JSON
$data = json_decode(file_get_contents('php://input'), true);
$schNum = $data['schNum'] ?? '';
$userId = $data['userId'] ?? '';

if (!$schNum || !$userId) {
    echo json_encode([
        "error" => true,
        "message" => "缺少必要的參數",
        "schNum" => $schNum,
        "userId" => $userId
    ]);
    exit;
}

// 查詢 todos 並合併使用者是否完成和是否已通知
$sql = "
    SELECT 
        t.todo_id,
        t.title,
        t.start_time,
        t.end_time,
        COALESCE(ut.is_done, 0) AS is_done,
        COALESCE(ut.is_notified, 0) AS is_notified
    FROM todos t
    LEFT JOIN user_todos ut 
        ON t.todo_id = ut.todo_id AND ut.user_id = ?
    WHERE t.sch_num = ?
    ORDER BY t.todo_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $userId, $schNum);
$stmt->execute();
$result = $stmt->get_result();

$todos = [];
while ($row = $result->fetch_assoc()) {
    $todos[] = $row;
}

echo json_encode($todos); // 返回 todo 項目，包括 is_notified
$stmt->close();
$conn->close();
?>
