<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "尚未登入";
    exit();
}

require_once("db.php");

$user_id = $_SESSION['user_id'];
$sch_num = $_POST['sch_num'];  // 前端傳過來的學校代碼

// 查詢該學校的 todos
$sql = "SELECT todo_id FROM todos WHERE sch_num = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();

// 將每個 todo_id 插入 user_todos 表
while ($row = $result->fetch_assoc()) {
    $todo_id = $row['todo_id'];

    // 插入到 user_todos 表
    $insert_sql = "INSERT INTO user_todos (user_id, todo_id, is_done, updated_at) VALUES (?, ?, 0, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $todo_id);

    if (!$insert_stmt->execute()) {
        echo "錯誤: " . $insert_stmt->error;
        exit();
    }
}

echo "已成功加入 todos";
$stmt->close();
$conn->close();
?>
