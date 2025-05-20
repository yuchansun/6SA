

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


    <style>
        /*防止表格標題換行，保持水平顯示 */
        th {
            white-space: nowrap;
        }

        /* 讓表格保持自動調整，但讓 td 寬度相同 */
.table {
    table-layout: auto;  /* 默認的自動調整佈局 */
}

.table th {
    white-space: nowrap; /* 防止 th 標題換行 */
    text-align: center;  /* 使內容居中 */
}

.table td {
    width: 50%; /* 保證 td 兩列寬度相等 */
    text-align: center;  /* 使內容居中 */
}

/* 調整表格的內邊距，增加左右的留白 */
.table th, .table td {
    padding: 15px 40px; /* 15px 上下，40px 左右 */
    text-align: center;  /* 使內容居中 */
}

/* 防止表格標題換行，保持水平顯示 */
.table th {
    white-space: nowrap;
}

/* 覆蓋 .table-dark 類的背景顏色為藍色 */
.table-dark, .table-dark th, .table-dark td {
            background-color: #1e4356 !important; /* 藍色背景 */
            color: white !important;  /* 文字顏色設為白色 */
        }

/* 新增專屬樣式給左欄的項目 */
.table th.item-label {
    background-color: #1e4356 !important;
    color: white !important;
    font-weight: bold;
    width: 15%; /* 可視情況調整 */
    text-align: center;
}



    </style>
</head>
<body class="about-page">

<main class="main">
    <div class="page-title dark-background">
        <div class="container position-relative">
            <h2>學校資料比較</h2>
        </div>
    </div>



    <div class="container mt-5">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>項目</th>
                    <th><?= htmlspecialchars($school1['School_Name']) ?> - <?= htmlspecialchars($school1['Department']) ?></th>
                    <th><?= htmlspecialchars($school2['School_Name']) ?> - <?= htmlspecialchars($school2['Department']) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="item-label">科系</th>
                    <td><?= htmlspecialchars($school1['Department']) ?></td>
                    <td><?= htmlspecialchars($school2['Department']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">地區</th>
                    <td><?= htmlspecialchars($school1['Region']) ?></td>
                    <td><?= htmlspecialchars($school2['Region']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">考試項目</th>
                    <td><?= htmlspecialchars($school1['Exam_Item']) ?></td>
                    <td><?= htmlspecialchars($school2['Exam_Item']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">考試時間</th>
                    <td><?= htmlspecialchars($school1['exam_date']) ?></td>
                    <td><?= htmlspecialchars($school2['exam_date']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">學群</th>
                    <td><?= htmlspecialchars($school1['Disc_Cluster']) ?></td>
                    <td><?= htmlspecialchars($school2['Disc_Cluster']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">名額</th>
                    <td><?= htmlspecialchars($school1['Quota']) ?></td>
                    <td><?= htmlspecialchars($school2['Quota']) ?></td>
                </tr>                               
                <tr>
                    <th class="item-label">能力</th>
                    <td><?= htmlspecialchars($school1['Talent']) ?></td>
                    <td><?= htmlspecialchars($school2['Talent']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">詳細資訊</th>
                    <td><?= htmlspecialchars($school1['requirement']) ?></td>
                    <td><?= htmlspecialchars($school2['requirement']) ?></td>
                </tr>
                <tr>
                    <th class="item-label">備註</th>
                    <td><?= htmlspecialchars($school1['note']) ?></td>
                    <td><?= htmlspecialchars($school2['note']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<footer class="footer mt-5">
    <div class="container text-center">
        <p>  </p>
    </div>
</footer>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

<!-- Footer -->
<?php include('footer.php'); ?>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>


</html>
