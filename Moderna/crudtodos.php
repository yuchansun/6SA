<?php
// Make sure no output is sent before we might need to send headers
ob_start();

include('header.php');

// 確認 session 中有 user_id
if (!isset($_SESSION['user_id'])) {
    die("無權訪問：session 中找不到 user_id");
}

$user_id = $_SESSION['user_id'];  // 確保 session 中有 user_id

// 連接資料庫
$conn = new mysqli('localhost', 'root', '', 'sa-6');
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 查詢用戶角色
$sql = "SELECT Roles FROM account WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 檢查是否成功獲取角色
if ($result && $row = $result->fetch_assoc()) {
    if ($row['Roles'] !== '管理者') {
        die("無權訪問：您不是管理者");
    }
} else {
    die("無法獲取角色資料，請檢查資料庫");
}

// 處理 AJAX 請求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Make sure we clear any previous output before setting headers
    ob_clean();
    header('Content-Type: application/json');
    
    // 添加 TODO
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sch_num = $_POST['sch_num'];
        $title = $_POST['title'];
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : NULL;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;
        
        // 檢查是否存在必要數據
        if (empty($sch_num) || empty($title)) {
            $response = [
                'success' => false,
                'message' => '校系編號和標題為必填欄位'
            ];
            echo json_encode($response);
            exit;
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO todos (Sch_num, title, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $sch_num, $title, $start_time, $end_time);
            
            $response = [];
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['todo_id'] = $conn->insert_id;
                $response['message'] = '新增成功';
                $response['sch_num'] = $sch_num; // 加入校系編號，以便前端進行跳轉
            } else {
                $response['success'] = false;
                $response['message'] = '資料庫執行錯誤: ' . $conn->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => '發生異常：' . $e->getMessage()
            ];
        }
        
        echo json_encode($response);
        exit;
    }

    // 刪除 TODO
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_todo_id'])) {
        $delete_id = intval($_POST['delete_todo_id']);

        // 獲取校系編號以便跳轉
        $get_sch_stmt = $conn->prepare("SELECT Sch_num FROM todos WHERE todo_id = ?");
        $get_sch_stmt->bind_param("i", $delete_id);
        $get_sch_stmt->execute();
        $get_sch_result = $get_sch_stmt->get_result();
        $sch_row = $get_sch_result->fetch_assoc();
        $sch_num = $sch_row ? $sch_row['Sch_num'] : '';
        $get_sch_stmt->close();

        $stmt = $conn->prepare("DELETE FROM todos WHERE todo_id = ?");
        $stmt->bind_param("i", $delete_id);
        
        $response = [];
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = '刪除成功';
            $response['sch_num'] = $sch_num;
        } else {
            $response['success'] = false;
            $response['message'] = '刪除失敗：' . $conn->error;
        }
        $stmt->close();
        
        echo json_encode($response);
        exit;
    }

    // 更新 TODO
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $todo_id = intval($_POST['todo_id']);
        $title = $_POST['title'];
        $start_time = $_POST['start_time'] ?? NULL;
        $end_time = $_POST['end_time'] ?? NULL;

        $start_time = empty($start_time) ? NULL : $start_time;
        $end_time = empty($end_time) ? NULL : $end_time;

        // 獲取校系編號以便返回
        $get_sch_stmt = $conn->prepare("SELECT Sch_num FROM todos WHERE todo_id = ?");
        $get_sch_stmt->bind_param("i", $todo_id);
        $get_sch_stmt->execute();
        $get_sch_result = $get_sch_stmt->get_result();
        $sch_row = $get_sch_result->fetch_assoc();
        $sch_num = $sch_row ? $sch_row['Sch_num'] : '';
        $get_sch_stmt->close();

        $stmt = $conn->prepare("UPDATE todos SET title = ?, start_time = ?, end_time = ? WHERE todo_id = ?");
        $stmt->bind_param("sssi", $title, $start_time, $end_time, $todo_id);

        $response = [];
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = '更新成功';
            $response['sch_num'] = $sch_num;
        } else {
            $response['success'] = false;
            $response['message'] = '更新失敗：' . $conn->error;
        }

        $stmt->close();
        echo json_encode($response);
        exit;
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

    $or_clauses = [];
    foreach ($search_terms as $term) {
        $like_term = "%" . $term . "%";
        $or_clauses[] = "(s.Sch_num LIKE ? OR s.School_Name LIKE ? OR s.Department LIKE ?)";
        array_push($params, $like_term, $like_term, $like_term);
        $types .= 'sss';
    }
    // 多關鍵字條件使用 OR 串接
    $where_clauses[] = "(" . implode(" OR ", $or_clauses) . ")";
}

$sql = "
    SELECT t.todo_id, t.title, t.start_time, t.end_time, s.Sch_num, s.School_Name, s.Department
    FROM sch_description s
    LEFT JOIN todos t ON s.Sch_num = t.Sch_num
";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY s.Sch_num, COALESCE(t.start_time, t.end_time)";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$todos = $stmt->get_result();

// 渲染搜尋框和新增表單（只輸出一次）

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

// 新增 TODO 表單 + datalist
echo "
<div class='add-todo-section'>
  <form id='addTodoForm'>
    <input list='sch_num_list' name='Sch_num' placeholder='學校代碼' required>
    <input type='text' name='title' placeholder='標題' required>
    <input type='datetime-local' name='start_time'>
    <input type='datetime-local' name='end_time'>
    <button type='submit'>新增</button>
  </form>

  <datalist id='sch_num_list'>";
  
  $result = $conn->query("SELECT Sch_num, School_Name, Department FROM sch_description");
  while ($row = $result->fetch_assoc()) {
      echo "<option value='" . htmlspecialchars($row['Sch_num']) . "'>" .
           htmlspecialchars($row['School_Name']) . " - " .
           htmlspecialchars($row['Department']) . "</option>";
  }

echo "
  </datalist>
</div>
";


$current_sch_num = null;
$has_data = false;
$printed_section = false; // 紀錄是否有開始 section

while ($row = $todos->fetch_assoc()) {
    if ($row["Sch_num"] !== $current_sch_num) {
        // 如果不是第一次校系，先處理前一個校系的結尾
        if ($current_sch_num !== null) {
            if (!$has_data) {
                echo "<tr><td colspan='5'>暫無資料</td></tr>";
            }
            echo "</tbody></table></section>";
        }

        // 開始新校系區塊
        $current_sch_num = $row["Sch_num"];
        $has_data = false;

        echo "<section id='" . htmlspecialchars($current_sch_num, ENT_QUOTES) . "' class='portfolio-details section'>";
        echo '<h3 style="color:white; display:inline-block; margin-right:10px;">' . 
             htmlspecialchars($current_sch_num . ' ' . $row["School_Name"] . ' ' . $row["Department"]) . 
             '</h3>';
        echo "<button class='add-todo-btn' data-sch_num='" . htmlspecialchars($current_sch_num, ENT_QUOTES) . "' title='新增 TODO' style='font-size:20px; cursor:pointer;'>＋</button>";

        echo "<table class='todo-table' data-sch_num='" . htmlspecialchars($current_sch_num, ENT_QUOTES) . "'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>標題</th>
                        <th>開始時間</th>
                        <th>結束時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody class='todo-body'>";
        $printed_section = true;
    }

    // 只要有 todo_id 就代表有資料
    if (!empty($row["todo_id"])) {
        $has_data = true;
        echo "<tr class='todo-item' id='todo-" . htmlspecialchars($row["todo_id"], ENT_QUOTES) . "'>
                <td>" . htmlspecialchars($row["todo_id"]) . "</td>
                <td class='todo-title'>" . htmlspecialchars($row["title"]) . "</td>
                <td class='todo-start-time'>" . htmlspecialchars($row["start_time"]) . "</td>
                <td class='todo-end-time'>" . htmlspecialchars($row["end_time"]) . "</td>
                <td class='crud-buttons'>
                    <button class='delete-btn' data-id='" . htmlspecialchars($row["todo_id"], ENT_QUOTES) . "'>刪除</button>
                    <button class='edit-btn' data-id='" . htmlspecialchars($row["todo_id"], ENT_QUOTES) . "'>修改</button>
                    <button class='cancel-btn' data-id='" . htmlspecialchars($row["todo_id"], ENT_QUOTES) . "' style='display:none;'>取消</button>
                    <button class='submit-btn' data-id='" . htmlspecialchars($row["todo_id"], ENT_QUOTES) . "' style='display:none;'>送出</button>
                </td>
              </tr>";
    }
}

// 最後一個校系結尾
if ($printed_section) {
    if (!$has_data) {
        echo "<tr><td colspan='5'>暫無資料</td></tr>";
    }
    echo "</tbody></table></section>";
}

echo "</div>"; // 如果有開過

$conn->close();

// Output buffer flush
ob_end_flush();
?>

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
    // 格式化日期時間
    function formatToDatetimeLocal(datetime) {
        if (!datetime) return '';
        const date = new Date(datetime);
        const offset = date.getTimezoneOffset();
        const localDate = new Date(date.getTime() - offset * 60000);
        return localDate.toISOString().slice(0, 16); // "YYYY-MM-DDTHH:MM"
    }

    // 捲動到指定位置的函數
    function scrollToElement(elementId) {
        const target = document.getElementById(elementId);
        if (target) {
            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }

    // 處理查詢字符串
    function getQueryParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // 用於顯示錯誤信息的函數
    function showError(error) {
        console.error('Error:', error);
        // 可以在這裡添加更多用戶友好的錯誤處理
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 處理頁面載入時的自動捲動
        const hash = window.location.hash;
        if (hash) {
            const targetId = hash.substring(1); // 移除 # 符號
            scrollToElement(targetId);
        }

        // 編輯按鈕事件
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const todoId = this.dataset.id;
                const row = document.getElementById('todo-' + todoId);
                const titleCell = row.querySelector('.todo-title');
                const startTimeCell = row.querySelector('.todo-start-time');
                const endTimeCell = row.querySelector('.todo-end-time');

                titleCell.setAttribute('data-original', titleCell.innerText);
                startTimeCell.setAttribute('data-original', startTimeCell.innerText);
                endTimeCell.setAttribute('data-original', endTimeCell.innerText);

                titleCell.setAttribute('contenteditable', 'true');
                titleCell.classList.add('editing-cell');

                const formattedStart = startTimeCell.innerText.trim() ? formatToDatetimeLocal(startTimeCell.innerText) : '';
                const formattedEnd = endTimeCell.innerText.trim() ? formatToDatetimeLocal(endTimeCell.innerText) : '';

                startTimeCell.innerHTML = `<input type="datetime-local" value="${formattedStart}">`;
                endTimeCell.innerHTML = `<input type="datetime-local" value="${formattedEnd}">`;

                startTimeCell.classList.add('editing-cell');
                endTimeCell.classList.add('editing-cell');

                row.querySelector('.cancel-btn').style.display = 'inline';
                row.querySelector('.submit-btn').style.display = 'inline';
                row.querySelector('.edit-btn').style.display = 'none';
            });
        });

        // 取消編輯按鈕事件
        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function () {
                const todoId = this.dataset.id;
                const row = document.getElementById('todo-' + todoId);
                const titleCell = row.querySelector('.todo-title');
                const startTimeCell = row.querySelector('.todo-start-time');
                const endTimeCell = row.querySelector('.todo-end-time');

                titleCell.innerText = titleCell.getAttribute('data-original');
                startTimeCell.innerText = startTimeCell.getAttribute('data-original');
                endTimeCell.innerText = endTimeCell.getAttribute('data-original');

                titleCell.removeAttribute('contenteditable');
                titleCell.classList.remove('editing-cell');
                startTimeCell.classList.remove('editing-cell');
                endTimeCell.classList.remove('editing-cell');

                row.querySelector('.cancel-btn').style.display = 'none';
                row.querySelector('.submit-btn').style.display = 'none';
                row.querySelector('.edit-btn').style.display = 'inline';
            });
        });

        // 送出更新按鈕事件
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

                // 使用當前頁面URL，確保請求發送到正確位置
                const currentUrl = window.location.pathname;
                
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('todo_id', todoId);
                formData.append('title', newTitle);
                formData.append('start_time', newStartTime);
                formData.append('end_time', newEndTime);
                
                fetch(currentUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('更新成功');
                        // 更新並捲動到位置
                        if (data.sch_num) {
                            window.location.href = window.location.pathname + '#' + data.sch_num;
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('更新失敗: ' + data.message);
                    }
                })
                .catch(error => {
                    showError(error);
                    alert('更新請求失敗，請檢查控制台了解詳情');
                });
            });
        });

        // 刪除按鈕事件
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!confirm('確定要刪除這筆 TODO 嗎？')) return;
                
                const todoId = this.dataset.id;
                const currentUrl = window.location.pathname;
                
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('delete_todo_id', todoId);
                
                fetch(currentUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('刪除成功');
                        if (data.sch_num) {
                            window.location.href = window.location.pathname + '#' + data.sch_num;
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('刪除失敗: ' + data.message);
                    }
                })
                .catch(error => {
                    showError(error);
                    alert('刪除請求失敗，請檢查控制台了解詳情');
                });
            });
        });

        // 新增 TODO 表單提交處理
        document.getElementById('addTodoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const schNum = this.querySelector('input[name="Sch_num"]').value.trim();
            const title = this.querySelector('input[name="title"]').value.trim();
            const startTime = this.querySelector('input[name="start_time"]').value;
            const endTime = this.querySelector('input[name="end_time"]').value;
            
            if (!schNum || !title) {
                alert('學校代碼和標題為必填欄位');
                return;
            }
            
            // 使用當前頁面URL，確保請求發送到正確位置
            const currentUrl = window.location.pathname; 
            
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('sch_num', schNum);
            formData.append('title', title);
            formData.append('start_time', startTime);
            formData.append('end_time', endTime);
            
            fetch(currentUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('新增成功');
                    // 跳轉到對應的校系錨點
                    window.location.href = window.location.pathname + '#' + schNum;
                    window.location.reload();
                } else {
                    alert('新增失敗: ' + (data.message || '未知錯誤'));
                }
            })
            .catch(error => {
                showError(error);
                alert('新增請求失敗，請檢查控制台了解詳情');
            });
        });

        // 表格內的新增按鈕事件
        document.querySelectorAll('.add-todo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const schNum = btn.dataset.sch_num;
                const table = document.querySelector(`table.todo-table[data-sch_num='${schNum}']`);
                const tbody = table.querySelector('.todo-body');

                if (tbody.querySelector('.new-todo-row')) return;

                const newRow = document.createElement('tr');
                newRow.classList.add('new-todo-row');
                newRow.innerHTML = `
                    <td>新</td>
                    <td><input type="text" class="new-title" placeholder="標題"></td>
                    <td><input type="datetime-local" class="new-start-time"></td>
                    <td><input type="datetime-local" class="new-end-time"></td>
                    <td style="text-align:center;">
                        <button class="save-new-btn">送出</button>
                        <button class="cancel-new-btn">取消</button>
                    </td>
                `;
                tbody.appendChild(newRow);

                const saveBtn = newRow.querySelector('.save-new-btn');
                const cancelBtn = newRow.querySelector('.cancel-new-btn');

                saveBtn.addEventListener('click', () => {
                    const title = newRow.querySelector('.new-title').value.trim();
                    const startTime = newRow.querySelector('.new-start-time').value;
                    const endTime = newRow.querySelector('.new-end-time').value;

                    if (!title) {
                        alert('標題不能空白');
                        return;
                    }

                    saveBtn.disabled = true;
                    
                    // 使用當前頁面URL
                    const currentUrl = window.location.pathname;
                   
                    
                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('sch_num', schNum);
                    formData.append('title', title);
                    formData.append('start_time', startTime);
                    formData.append('end_time', endTime);

                    fetch(currentUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('新增成功');
                            window.location.href = window.location.pathname + '#' + schNum;
                            window.location.reload();
                        } else {
                            alert('新增失敗: ' + (data.message || ''));
                            saveBtn.disabled = false;
                        }
                    })
                    .catch((error) => {
                        console.error('錯誤：', error);
                        alert('新增失敗，請查看控制台');
                        saveBtn.disabled = false;
                    });
            });

            cancelBtn.addEventListener('click', () => {
                newRow.remove();
            });
        });
    });
});
</script>


</body>

</html>
<?php include('footer.php'); ?>

<?php