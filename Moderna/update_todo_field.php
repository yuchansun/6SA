<?php
// 連接資料庫
$conn = new mysqli('localhost', 'root', '', 'sa-6');
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 確認接收到的資料
if (isset($_POST['todo_id']) && isset($_POST['title'])) {
    $todo_id = $_POST['todo_id'];
    $title = $conn->real_escape_string($_POST['title']);
    
    // 如果 start_time 和 end_time 是空的，設為 NULL
    $start_time = empty($_POST['start_time']) ? "NULL" : "'" . $conn->real_escape_string($_POST['start_time']) . "'";
    $end_time = empty($_POST['end_time']) ? "NULL" : "'" . $conn->real_escape_string($_POST['end_time']) . "'";

    // 更新資料庫
    $update_query = "
        UPDATE todos 
        SET title = '$title', start_time = $start_time, end_time = $end_time
        WHERE todo_id = $todo_id
    ";

    if ($conn->query($update_query)) {
        echo 'success';
    } else {
        echo '更新失敗: ' . $conn->error;
    }
} else {
    echo '缺少必要的參數';
}

$conn->close();
?>
