<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$schNum = $data['schNum'];
$index = $data['index'];  // 如果你想要使用 index 作為判斷依據，也可以從資料庫中取得具體的 todo ID

$conn = new mysqli('localhost', 'username', 'password', 'database');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 假設你的資料表有個 ID 欄位，可以根據這個欄位來更新
$sql = "UPDATE todos SET completed = NOT completed WHERE Sch_num = ? AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $schNum, $index);
$stmt->execute();

echo json_encode(['success' => true]);

$stmt->close();
$conn->close();
?>
<script>
    function toggleComplete(schNum, index) {
  const list = document.getElementById(`todo-${schNum}`);
  if (!list) {
    console.error(`找不到 todo list 元素: todo-${schNum}`);
    return;
  }

  // 取得已儲存的 To-do 列表
  let todos = JSON.parse(localStorage.getItem(`todo-${schNum}`)) || [];
  
  // 更新該項 To-do 的 completed 狀態
  todos[index].completed = !todos[index].completed;

  // 更新本地儲存的 To-do 列表
  localStorage.setItem(`todo-${schNum}`, JSON.stringify(todos));

  // 發送更新請求到後端，更新資料庫中的 completed 狀態
  fetch('update_todo.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      schNum: schNum,
      todoIndex: index,
      completed: todos[index].completed
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log('後端更新成功:', data);
  })
  .catch(error => {
    console.error('發生錯誤:', error);
  });

  renderTodos(schNum);  // 重新渲染 To-do 列表
}
</script>