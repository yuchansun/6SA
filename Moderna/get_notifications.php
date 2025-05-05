<?php
// 顯示錯誤以便除錯
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php'; // 資料庫連接檔案
session_start();

// 檢查是否有登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// 這邊是變數定義，不可漏掉！
$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d'); // 取得今天日期

// 查詢三天內要開始或結束的 todo，且已啟用通知
$query = "
    SELECT sd.School_Name,sd.Department, t.title, t.end_time, ut.todo_id
    FROM user_todos ut
    JOIN todos t ON ut.todo_id = t.todo_id
    JOIN sch_description sd ON t.Sch_num = sd.Sch_num
    WHERE ut.user_id = ? 
    AND ut.is_done = 0 
    AND ut.is_notified = 1
    AND (
        (DATEDIFF(t.start_time, ?) <= 3 AND DATEDIFF(t.start_time, ?) >= 0) 
        OR (DATEDIFF(t.end_time, ?) <= 3 AND DATEDIFF(t.end_time, ?) >= 0)
    )
";

$stmt = $conn->prepare($query);
$stmt->bind_param('sssss', $user_id, $current_date, $current_date, $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['todo_id'],
        'title' => $row['title'],
        'end_time' => $row['end_time'],
        'School_Name' => $row['School_Name'],
        'Department' => $row['Department'],
    ];
    
}

$stmt->close();

// 正確回傳 JSON
header('Content-Type: application/json');
echo json_encode($notifications);
?>
