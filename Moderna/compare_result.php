<?php
session_start();
require_once 'db.php'; // 資料庫連接

// 取得傳遞的學校編號
$sch1 = $_GET['sch1'] ?? null;
$sch2 = $_GET['sch2'] ?? null;

// 檢查是否有兩個學校編號
if (!$sch1 || !$sch2) {
    echo "請選擇兩所學校進行比較。";
    exit;
}

// 查詢兩所學校的資料
$sql = "SELECT * FROM sch_description WHERE Sch_num IN (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $sch1, $sch2); // 綁定學校編號參數
$stmt->execute();
$result = $stmt->get_result();

$schools = [];
while ($row = $result->fetch_assoc()) {
    $schools[] = $row;
}

$stmt->close();

// 如果找不到兩所學校的資料
if (count($schools) !== 2) {
    echo "未找到比較的學校資料。";
    exit;
}

// 取得學校資料
$school1 = $schools[0];
$school2 = $schools[1];

// 顯示頁面內容
?>

<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>學校比較</title>
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="result-page">

<main class="main">
    <div class="page-title dark-background">
        <div class="container position-relative">
            <h2>學校資料比較</h2>
        </div>
    </div>

    <div class="container mt-5">
        <table class="table table-striped">
            <thead class="thead dark-background">
                <tr>
                    <th>項目</th>
                    <th><?= htmlspecialchars($school1['School_Name']) ?> - <?= htmlspecialchars($school1['Department']) ?></th>
                    <th><?= htmlspecialchars($school2['School_Name']) ?> - <?= htmlspecialchars($school2['Department']) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>學校</th>
                    <td><?= htmlspecialchars($school1['School_Name']) ?></td>
                    <td><?= htmlspecialchars($school2['School_Name']) ?></td>
                </tr>
                <tr>
                    <th>科系</th>
                    <td><?= htmlspecialchars($school1['Department']) ?></td>
                    <td><?= htmlspecialchars($school2['Department']) ?></td>
                </tr>
                <tr>
                    <th>地區</th>
                    <td><?= htmlspecialchars($school1['Region']) ?></td>
                    <td><?= htmlspecialchars($school2['Region']) ?></td>
                </tr>
                <tr>
                    <th>學群</th>
                    <td><?= htmlspecialchars($school1['Disc_Cluster']) ?></td>
                    <td><?= htmlspecialchars($school2['Disc_Cluster']) ?></td>
                </tr>
                <tr>
                    <th>名額</th>
                    <td><?= htmlspecialchars($school1['Quota']) ?></td>
                    <td><?= htmlspecialchars($school2['Quota']) ?></td>
                </tr>
                <tr>
                    <th>聯繫方式</th>
                    <td><?= htmlspecialchars($school1['Contact']) ?></td>
                    <td><?= htmlspecialchars($school2['Contact']) ?></td>
                </tr>
                <tr>
                    <th>詳細資訊</th>
                    <td><?= htmlspecialchars($school1['requirement']) ?></td>
                    <td><?= htmlspecialchars($school2['requirement']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<footer class="footer mt-5">
    <div class="container text-center">
        <p>© 2025 學校比較系統</p>
    </div>
</footer>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
