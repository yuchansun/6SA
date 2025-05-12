<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("無權訪問：session 中找不到 user_id");
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'sa-6');
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 刪除 TODO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_todo_id'])) {
    $delete_id = intval($_POST['delete_todo_id']);

    $get_sch_stmt = $conn->prepare("SELECT Sch_num FROM todos WHERE todo_id = ?");
    $get_sch_stmt->bind_param("i", $delete_id);
    $get_sch_stmt->execute();
    $get_sch_stmt->bind_result($sch_num);
    $get_sch_stmt->fetch();
    $get_sch_stmt->close();

    $stmt = $conn->prepare("DELETE FROM todos WHERE todo_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>
            alert('刪除成功！');
            window.location.href = window.location.href.split('?')[0] + '?sch_num=" . $sch_num . "#' + '" . $sch_num . "';
        </script>";
        exit;
    } else {
        echo "刪除失敗：" . $conn->error;
    }
    $stmt->close();
}
// 更新 TODO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $todo_id = intval($_POST['todo_id']);
    $title = $_POST['title'];
    $start_time = $_POST['start_time'] ?? NULL;
    $end_time = $_POST['end_time'] ?? NULL;

    $start_time = empty($start_time) ? NULL : $start_time;
    $end_time = empty($end_time) ? NULL : $end_time;

    $stmt = $conn->prepare("UPDATE todos SET title = ?, start_time = ?, end_time = ? WHERE todo_id = ?");
    $stmt->bind_param("sssi", $title, $start_time, $end_time, $todo_id);

    if ($stmt->execute()) {
        echo "更新成功";
    } else {
        echo "更新失敗：" . $conn->error;
    }

    $stmt->close();
    exit;
}



// 新增 TODO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $error_message = ''; // 用來儲存錯誤訊息

    // 檢查標題和學校編號是否填寫
    if (empty($_POST['title']) || empty($_POST['Sch_num'])) {
        $error_message = "請填寫標題和學校編號";
    }

    // 檢查至少填寫 start_time 或 end_time
    $start_time = $_POST['start_time'] ?: NULL;
    $end_time = $_POST['end_time'] ?: NULL;

    if (empty($start_time) && empty($end_time)) {
        $error_message = "請填寫開始時間或結束時間其中一個";
    }

    // 檢查時間格式
    if ($start_time && $end_time && strtotime($start_time) > strtotime($end_time)) {
        echo "<script>alert('開始時間不能晚於結束時間');</script>";
        exit;
    }

    // 如果沒有錯誤，進行資料庫操作
    if ($error_message) {
        // 顯示錯誤訊息並停止執行
        echo "<script>alert('$error_message');</script>";
    } else {
        $title = $_POST['title'];
        $sch_num = $_POST['Sch_num'];

        // 確認 Sch_num 是否是有效選項
        $stmt = $conn->prepare("SELECT COUNT(*) FROM sch_description WHERE Sch_num = ?");
        $stmt->bind_param("s", $sch_num);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            echo "<script>alert('無效的學校編號');</script>";
        } else {
            // 新增 TODO
            $stmt = $conn->prepare("INSERT INTO todos (title, start_time, end_time, Sch_num) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $start_time, $end_time, $sch_num);

            if ($stmt->execute()) {
                echo "<script>alert('新增成功！');</script>";
                // 跳轉回目標頁面，並傳遞學校編號
                echo "<script>window.location.href='crudtodos.php?sch_num=$sch_num';</script>";
            } else {
                echo "<script>alert('新增失敗：" . $stmt->error . "');</script>";
            }

            $stmt->close();
        }
    }
}

// 搜尋條件
$search_terms = [];
$params = [];
$types = '';
$where_clauses = [];

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_input = trim($_GET['search']);
    $search_terms = preg_split('/\s+/', $search_input); // 支援多關鍵字

    foreach ($search_terms as $term) {
        $like_term = "%" . $term . "%";
        $where_clauses[] = "(s.Sch_num LIKE ? OR s.School_Name LIKE ? OR s.Department LIKE ?)";
        array_push($params, $like_term, $like_term, $like_term);
        $types .= 'sss'; // 每個 term 有三個欄位需要比對
    }
}

$sql = "
    SELECT t.todo_id, t.title, t.start_time, t.end_time, t.Sch_num, s.School_Name, s.Department
    FROM todos t
    JOIN sch_description s ON t.Sch_num = s.Sch_num
";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY t.Sch_num, COALESCE(t.start_time, t.end_time)";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$todos = $stmt->get_result();

if (!$todos) {
    die("查詢 todos 失敗：" . $conn->error);
}

$current_sch_num = null;



echo "<div class='container'>";

// 搜尋框
echo "
<div class='search-widget widget-item'>
  <form action='' method='GET'>
    <input type='text' name='search' placeholder='搜尋校系編號、學校名稱或科系名稱' value='" . (isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '') . "'>
    <button type='submit' title='搜尋'><i class='bi bi-search'></i>搜尋</button>
  </form>
</div>
";

// 新增 TODO 表單
echo "
<div style='margin-bottom: 20px;'>
  <h4>新增 TODO</h4>
  <form action='' method='POST'>
    <input type='hidden' name='action' value='add'> <!-- 加上這一行讓 PHP 知道是新增 -->
    
    <input list='sch_num_list' name='Sch_num' placeholder='學校代碼' required>
    <datalist id='sch_num_list'>
";

$result = $conn->query("SELECT Sch_num, School_Name, Department FROM sch_description");
while ($row = $result->fetch_assoc()) {
    echo "<option value='" . htmlspecialchars($row['Sch_num']) . "'>" . 
         htmlspecialchars($row['School_Name']) . " - " . 
         htmlspecialchars($row['Department']) . "</option>";
}

echo "
    </datalist>

    <input type='text' name='title' placeholder='標題' required>
    <input type='datetime-local' name='start_time'>
    <input type='datetime-local' name='end_time'>

    <button type='submit'>新增</button>
  </form>
</div>
";

while ($row = $todos->fetch_assoc()) {
    if ($row["Sch_num"] !== $current_sch_num) {
        if ($current_sch_num !== null) {
            echo "</table></section>";
        }
        echo "<section id='{$row["Sch_num"]}' class='portfolio-details section'>";
        echo '<h3 style="color:white">' . $row["Sch_num"] . ' ' . $row["School_Name"] . ' ' . $row["Department"] . '</h3>';
        echo "<table class='todo-table'>
                <tr>
                    <th>ID</th><th>標題</th><th>開始</th><th>結束</th><th>操作</th>
                </tr>";

        $current_sch_num = $row["Sch_num"];
    }

    // 顯示待辦事項
    echo "<tr class='todo-item' id='todo-{$row["todo_id"]}'>";
    echo "<td>" . $row["todo_id"] . "</td>";
    echo "<td class='todo-title'>" . htmlspecialchars($row["title"]) . "</td>";
    echo "<td class='todo-start-time'>" . $row["start_time"] . "</td>";
    echo "<td class='todo-end-time'>" . $row["end_time"] . "</td>";
    
    echo "<td class='crud-buttons'>
        <form method='POST' action='' onsubmit='return confirm(\"確定要刪除這筆 TODO 嗎？\")' style='display:inline;'>
            <input type='hidden' name='action' value='delete'>
            <input type='hidden' name='delete_todo_id' value='" . $row["todo_id"] . "'>
            <button type='submit' class='delete-btn'>刪除</button>
        </form>

        <button class='edit-btn' data-id='" . $row["todo_id"] . "'>修改</button>
        <button class='cancel-btn' data-id='" . $row["todo_id"] . "' style='display:none;'>取消</button>
        <button class='submit-btn' data-id='" . $row["todo_id"] . "' style='display:none;'>送出</button>
    </td>";

    echo "</tr>";
}
echo "</table></section>";
echo "</div>";
echo "
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const target = document.querySelector(hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
});
</script>
";

$conn->close();
?>





<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>校系簡章</title>
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<style>
/* 頁首固定樣式 */
body #header {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 999;
    background: rgba(0, 55, 67, 0.95);
}

/* 容器，避免與固定頁首重疊 */
.container {
    margin-top: 50px;
    padding-top: 100px; /* 根據需要調整 */
}

/* 編輯單元格的樣式 */
.editing-cell {
    border: 2px solid black;
    background-color: #fffbe6; /* 使編輯中的單元格顯眼 */
}

.editing-cell input {
    border: none;
    outline: none;
    width: 100%;
    background-color: #fffbe6;
}

/* 按鈕樣式 */
.edit-btn, .cancel-btn, .submit-btn, .delete-btn {
    background-color: rgb(59, 59, 60);
    color: white;
    border: none;
    border-radius: 10px;
}

.edit-btn:hover, .cancel-btn:hover, .submit-btn:hover, .delete-btn:hover {
    background-color: rgb(165, 165, 167);
    cursor: pointer;
}

/* 刪除按鈕特殊樣式 */
.delete-btn {
    background-color: rgb(234, 56, 56);
}

/* 表格容器，確保可水平滾動 */
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* 固定表格列寬 */
table {
    table-layout: fixed;
    width: 100%;
}

/* 表格標題和單元格的樣式 */
th, td {
    padding: 10px;
    word-wrap: break-word;
    width: 20%; /* 假設表格有5列，這裡將每列寬度設為20% */
}

/* 標題樣式 */
h3 {
    background-color: var(--heading-color);
    color: white;
    padding: 10px;
    margin-top: 0;
}

/* 代辦清單表格樣式 */
.todo-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.todo-table th, .todo-table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

.todo-table th {
    background-color: #f2f2f2;
}



/* CRUD 按鈕的樣式 */
.crud-buttons form {
    display: inline;
}

.crud-buttons button {
    margin: 5px;
}

/* 手機版響應式設計 */
@media screen and (max-width: 768px) {
    .container {
        width: 100%;
        padding: 10px;
    }

    .todo-table th, .todo-table td {
        font-size: 12px;
    }

    .crud-buttons {
        text-align: center;
    }

    .crud-buttons button {
        display: block;
        margin: 5px 0;
        width: 100%;
    }
}
</style>


<script>
        function formatToDatetimeLocal(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            const offset = date.getTimezoneOffset();
            const localDate = new Date(date.getTime() - offset * 60000);
            return localDate.toISOString().slice(0, 16); // "YYYY-MM-DDTHH:MM"
        }

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const todoId = this.dataset.id;
                const row = document.getElementById('todo-' + todoId);
                const titleCell = row.querySelector('.todo-title');
                const startTimeCell = row.querySelector('.todo-start-time');
                const endTimeCell = row.querySelector('.todo-end-time');

                // 儲存原始資料
                titleCell.setAttribute('data-original', titleCell.innerText);
                startTimeCell.setAttribute('data-original', startTimeCell.innerText);
                endTimeCell.setAttribute('data-original', endTimeCell.innerText);

                // 可編輯樣式
                titleCell.setAttribute('contenteditable', 'true');
                titleCell.classList.add('editing-cell');

                const formattedStart = startTimeCell.innerText.trim() ? formatToDatetimeLocal(startTimeCell.innerText) : '';
                const formattedEnd = endTimeCell.innerText.trim() ? formatToDatetimeLocal(endTimeCell.innerText) : '';

                startTimeCell.innerHTML = `<input type="datetime-local" value="${formattedStart}">`;
                endTimeCell.innerHTML = `<input type="datetime-local" value="${formattedEnd}">`;

                startTimeCell.classList.add('editing-cell');
                endTimeCell.classList.add('editing-cell');

                // 顯示按鈕
                row.querySelector('.cancel-btn').style.display = 'inline';
                row.querySelector('.submit-btn').style.display = 'inline';
                row.querySelector('.edit-btn').style.display = 'none';
            });
        });

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function () {
                const todoId = this.dataset.id;
                const row = document.getElementById('todo-' + todoId);
                const titleCell = row.querySelector('.todo-title');
                const startTimeCell = row.querySelector('.todo-start-time');
                const endTimeCell = row.querySelector('.todo-end-time');

                // 還原原始內容
                titleCell.innerText = titleCell.getAttribute('data-original');
                startTimeCell.innerText = startTimeCell.getAttribute('data-original');
                endTimeCell.innerText = endTimeCell.getAttribute('data-original');

                // 移除可編輯和樣式
                titleCell.removeAttribute('contenteditable');
                titleCell.classList.remove('editing-cell');
                startTimeCell.classList.remove('editing-cell');
                endTimeCell.classList.remove('editing-cell');

                // 還原按鈕狀態
                row.querySelector('.cancel-btn').style.display = 'none';
                row.querySelector('.submit-btn').style.display = 'none';
                row.querySelector('.edit-btn').style.display = 'inline';
            });
        });

       document.querySelectorAll('.submit-btn').forEach(button => {
    button.addEventListener('click', function () {
        const todoId = this.dataset.id;
        const row = document.getElementById('todo-' + todoId);
        const titleCell = row.querySelector('.todo-title');
        const startTimeInput = row.querySelector('.todo-start-time input');
        const endTimeInput = row.querySelector('.todo-end-time input');

        const newTitle = titleCell.innerText.trim();
        const newStartTime = startTimeInput ? startTimeInput.value : '';
        const newEndTime = endTimeInput ? endTimeInput.value : '';

        // 建立表單資料
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('todo_id', todoId);
        formData.append('title', newTitle);
        formData.append('start_time', newStartTime);
        formData.append('end_time', newEndTime);

        // 傳送資料給後端 PHP
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            console.log(result);
            location.reload(); // 更新畫面
        })
        .catch(error => {
            console.error('錯誤：', error);
            alert('更新失敗');
        });
    });
});
        </script>
</body>

</html>
<?php include('footer.php'); ?>

<?php
