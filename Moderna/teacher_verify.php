<?php
session_start();

// 確認 session 中有 user_id
if (!isset($_SESSION['user_id'])) {
    die("無權訪問：session 中找不到 user_id");
}

$user_id = $_SESSION['user_id'];  // 確保 session 中有 user_id
// echo "用戶 ID: $user_id";  // 用來檢查 user_id 是否正確

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
    // echo "用戶角色: " . $row['Roles'];  // 用來檢查是否正確取得角色
    if ($row['Roles'] !== '管理者') {
        die("無權訪問：您不是管理者");
    }
} else {
    die("無法獲取角色資料，請檢查資料庫");
}

// 查詢未驗證的教師資料
$teachers = $conn->query("
    SELECT t.id, a.Nickname, a.`E-mail`, t.school_name, t.department, t.employment_status 
    FROM teacher_info t
    JOIN account a ON t.account_id = a.User_ID
    WHERE t.verified = 0
");

// 查詢已審核的教師資料
$approved_teachers = $conn->query("
    SELECT t.id, a.Nickname, a.`E-mail`, t.school_name, t.department, t.employment_status 
    FROM teacher_info t
    JOIN account a ON t.account_id = a.User_ID
    WHERE t.verified = 1
");

// 查詢已拒絕的教師資料
$rejected_teachers = $conn->query("
SELECT t.id, a.Nickname, a.`E-mail`, t.school_name, t.department, t.employment_status 
FROM teacher_info t
JOIN account a ON t.account_id = a.User_ID
WHERE t.verified = -1");



// 檢查查詢結果是否成功
if (!$teachers) {
    die("查詢教師資料失敗：" . $conn->error);
}


?>


<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>教師審核清單</title>

    <!-- Favicons -->
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Poppins&family=Raleway&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h3>待審核教師清單</h3>
            <a href="https://udb.moe.edu.tw/ulist/Teacher" class="btn btn-info btn-sm"><b>教師查詢連結🔗</b></a>
        </div>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>學校</th>
                        <th>系所</th>
                        <th>任職狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Nickname']) ?></td>
                            <td><?= htmlspecialchars($row['E-mail']) ?></td>
                            <td><?= htmlspecialchars($row['school_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['employment_status']) ?></td>
                            <td>
                                <form method="post" action="approve_teacher.php" style="display:inline;">
                                    <input type="hidden" name="teacher_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-custom btn-sm">通過</button>
                                </form>
                                <form method="post" action="reject_teacher.php" style="display:inline;">
                                    <input type="hidden" name="teacher_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">拒絕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <br></br>
        <!-- 已審核教師清單 -->
        <details>
            <summary><h3>已審核教師清單</h3></summary>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>學校</th>
                        <th>系所</th>
                        <th>任職狀態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $approved_teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Nickname']) ?></td>
                            <td><?= htmlspecialchars($row['E-mail']) ?></td>
                            <td><?= htmlspecialchars($row['school_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['employment_status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </details>

        <!-- 已拒絕教師清單 -->
        <details>
            <summary><h3>已拒絕教師清單</h3></summary>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>學校</th>
                        <th>系所</th>
                        <th>任職狀態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $rejected_teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Nickname']) ?></td>
                            <td><?= htmlspecialchars($row['E-mail']) ?></td>
                            <td><?= htmlspecialchars($row['school_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['employment_status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </details>
    </div>




    <style>
        body #header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
            background: rgba(0, 55, 67, 0.95);
        }

        .container {
            margin-top: 100px;
            padding-top: 100px;
            /* 或根據需要調整 */
            /* Adjust for fixed header */
        }

        .btn-custom {
            background-color: rgb(59, 59, 60);
            /* 綠色 */
            color: white;
            border: none;
            /* 可選，去除邊框 */
        }

        /* 設定 hover 狀態的效果 */
        .btn-custom:hover {
            background-color: rgb(165, 165, 167);
            /* 當滑鼠懸停時的顏色 */
            cursor: pointer;
            /* 改變游標為指標 */
        }

        .btn {
            border-radius: 20px;
            /* 圓角邊框 */
            font-size: 16px;
            /* 設定字體大小 */
            transition: all 0.3s ease;
            /* 平滑過渡效果 */
            border: none;
        }

        .btn-info {
            background-color: rgb(219, 219, 219);

        }

        .btn-info:hover {
            background-color: rgb(166, 167, 167);
        }

        .btn-danger {
            background-color: rgb(144, 147, 148);
        }

        .btn-danger:hover {
            background-color: rgb(78, 80, 80);
        }

        /* 包裝表格容器，確保表格在小螢幕上可以水平滾動 */
        .table-container {
            overflow-x: auto;
            /* 使表格在小螢幕上可以水平滾動 */
            -webkit-overflow-scrolling: touch;
            /* 為觸控設備啟用平滑滾動 */
        }

        /* 固定表格列寬，並設置表格為100%寬度 */
        table {
            table-layout: fixed;
            /* 固定表格列寬 */
            width: 100%;
            /* 表格寬度佔滿父容器 */
        }

        /* 表頭和表格單元格的樣式 */
        th,
        td {
            padding: 10px;
            /* 增加內邊距 */
            /* 可選：使文字居中對齊 */
            word-wrap: break-word;
            /* 使長文字換行 */
        }

        /* 自訂列寬，確保每列的寬度相同 */
        th,
        td {
            width: 20%;
            /* 假設表格有5列，這裡將每列寬度設為20% */
        }

        /* 設定折疊按鈕三角形 */
        details {
            margin-bottom: 20px;
            /* 增加清單間距 */
        }

        summary {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        summary::before {
            content: "▶";
            /* 預設為右邊的三角形 */
            margin-right: 10px;
            font-size: 18px;
            transition: transform 0.2s ease;
        }

        details[open] summary::before {
            transform: rotate(90deg);
            /* 打開時將三角形旋轉 */
        }

        /* 響應式設計：當螢幕小於某個寬度時，調整表格樣式 */
        @media (max-width: 768px) {

            th,
            td {
                font-size: 12px;
                /* 讓文字在小螢幕上更小 */
                padding: 8px;
                /* 減小內邊距，避免擠壓 */
            }
        }
    </style>
</body>

</html>
<?php include('footer.php'); ?>

<?php
