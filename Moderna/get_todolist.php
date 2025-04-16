<?php
header('Content-Type: application/json');

// 引入資料庫連接設定
include('db.php');

// 取得前端發送的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$schNum = $data['schNum'];  // 取得校系編號

// 查詢學校和其對應的 To-do list
$sql = "
    SELECT t.text, t.completed
    FROM todos t
    INNER JOIN sch_description s ON t.Sch_num = s.Sch_num
    WHERE t.Sch_num = ?
";
$stmt = $conn->prepare($sql);

// 假設 Sch_num 是字串型別，使用 's'；如果是整數型別，請使用 'i'
$stmt->bind_param('s', $schNum);  // 使用 's' 代表字串，改為 'i' 如果是整數

$stmt->execute();
$result = $stmt->get_result();

$todolist = [];
while ($row = $result->fetch_assoc()) {
    $todolist[] = $row;  // 將結果加入 to-do list 陣列
}

// 回傳 JSON 格式的資料
echo json_encode($todolist);

$stmt->close();
$conn->close();
?>
