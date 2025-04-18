<?php
file_put_contents("debug.log", print_r($_POST, true));

session_start();
if (!isset($_SESSION['user_id'])) {
    echo "尚未登入";
    exit();
}

require_once("db.php");

$user_id = $_SESSION['user_id'];
$sch_num = $_POST['sch_num'] ?? '';

if (empty($sch_num)) {
    echo "❌ 缺少 sch_num";
    exit();
}

$log = []; // 儲存除錯訊息

// 查詢該學校的 todos
$sql = "SELECT todo_id FROM todos WHERE sch_num = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "❌ Prepare 失敗：" . $conn->error;
    exit();
}
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;

while ($row = $result->fetch_assoc()) {
    $todo_id = $row['todo_id'];

    // 避免重複
    $check_sql = "SELECT 1 FROM user_todos WHERE user_id = ? AND todo_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $todo_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $insert_sql = "INSERT INTO user_todos (user_id, todo_id, is_done, updated_at) VALUES (?, ?, 0, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            $log[] = "❌ 插入 prepare 失敗：" . $conn->error;
            continue;
        }
        $insert_stmt->bind_param("ii", $user_id, $todo_id);
        if ($insert_stmt->execute()) {
            $count++;
            $log[] = "✅ 新增 todo_id=$todo_id 成功";
        } else {
            $log[] = "❌ 插入 todo_id=$todo_id 失敗：" . $insert_stmt->error;
        }
    } else {
        $log[] = "🔁 已存在 todo_id=$todo_id，跳過";
    }
}

echo "總共新增 $count 筆<br>";
foreach ($log as $msg) {
    echo $msg . "<br>";
}

$conn->close();
?>
