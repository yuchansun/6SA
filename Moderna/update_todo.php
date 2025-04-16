<?php
header('Content-Type: application/json');

// 引入資料庫連接設定
include('db.php');

// 取得前端發送的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$schNum = $data['schNum'];           // 取得校系編號
$todoIndex = $data['todoIndex'];     // 取得 To-do 索引
$completed = $data['completed'];     // 取得勾選狀態（true 或 false）

// 查詢該 To-do 是否存在，並更新其完成狀態
$sql = "UPDATE todos SET completed = ? WHERE Sch_num = ? AND todo_index = ?";
$stmt = $conn->prepare($sql);

// 假設 completed 是布林值（0 或 1），如果是 true 則設為 1，否則設為 0
$completedInt = $completed ? 1 : 0;

// 綁定參數並執行更新
$stmt->bind_param('isi', $completedInt, $schNum, $todoIndex);  // 參數依次為：布林值、字串、整數
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // 如果有更新，回傳成功訊息
    echo json_encode(['status' => 'success', 'message' => 'To-do 完成狀態更新成功']);
} else {
    // 如果沒有更新，回傳失敗訊息
    echo json_encode(['status' => 'error', 'message' => '更新失敗']);
}

$stmt->close();
$conn->close();
?>
