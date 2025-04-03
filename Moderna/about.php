<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>About - Moderna Bootstrap Template</title>
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

<body class="about-page">

<main class="main">
  <div class="page-title dark-background">
    <div class="container position-relative">
      <h1>校系簡章</h1>
      <p>基本的特殊選才資訊查詢功能，讓使用者可以透過關鍵字與篩選條件找到適合的學校與學程。</p>
    </div>
  </div>

  <section id="about" class="about section">
    <div class="container">
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sa-6";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("資料庫連線失敗: " . $conn->connect_error);

$filters = [
  "q" => $_GET['q'] ?? "",
  "region" => $_GET['region'] ?? "",
  "department" => $_GET['department'] ?? "",
  "plan" => $_GET['plan'] ?? "",
  "schol_apti" => $_GET['schol_apti'] ?? "",
  "talent" => $_GET['talent'] ?? "",
  "identity" => $_GET['ID'] ?? "",
  "school_name" => $_GET['school_name'] ?? "",
  "disc_cluster" => $_GET['disc_cluster'] ?? ""
];

// 查詢語法包含 JOIN admi_thro_years
$sql = "SELECT sd.*, aty.110, aty.111, aty.112, aty.113, aty.114 
        FROM sch_description sd 
        LEFT JOIN admi_thro_years aty ON sd.Sch_num = aty.sch_num 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($filters["q"])) {
  $cols = ["Sch_num", "School_Name", "Department", "Region", "Disc_Cluster", "Schol_Apti", "Talent", "ID", "Plan", "Quota", "Contact", "link"];
  $sql .= " AND (" . implode(" OR ", array_map(fn($c) => "$c LIKE ?", $cols)) . ")";
  foreach ($cols as $c) {
    $params[] = "%" . $filters["q"] . "%";
    $types .= "s";
  }
}
foreach ($filters as $k => $v) {
  if ($k !== "q" && !empty($v)) {
    $sql .= " AND $k = ?";
    $params[] = $v;
    $types .= "s";
  }
}

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL 錯誤: " . $conn->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>

<!-- 篩選 UI（略）... -->

<?php if (!empty($results)): ?>
<table class="table table-striped table-hover align-middle text-center mt-4">
  <thead class="table-dark">
    <tr><th>學校</th><th>科系</th><th>名額</th><th>詳細資料</th><th>收藏</th></tr>
  </thead>
  <tbody>
<?php foreach ($results as $row): ?>
<tr>
  <td><?= htmlspecialchars($row['School_Name']); ?></td>
  <td><?= htmlspecialchars($row['Department']); ?></td>
  <td><?= htmlspecialchars($row['Quota']); ?></td>
  <td>
    <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= $row['Sch_num']; ?>">
      詳細介紹
    </button>
  </td>
  <td>
    <button class="btn btn-outline-danger btn-sm favorite-btn" data-sch-num="<?= $row['Sch_num']; ?>">
      <i class="bi bi-heart"></i>
    </button>
  </td>
</tr>
<tr class="collapse" id="details-<?= $row['Sch_num']; ?>">
  <td colspan="5">
    <div class="card card-body">
      <div class="row">
        <div class="col-md-4 text-start">
          <p><strong>學校：</strong><?= htmlspecialchars($row['School_Name']); ?></p>
          <p><strong>科系：</strong><?= htmlspecialchars($row['Department']); ?></p>
          <p><strong>名額：</strong><?= htmlspecialchars($row['Quota']); ?></p>
          <p><strong>電話：</strong><?= htmlspecialchars($row['Contact']); ?></p>
        </div>
        <div class="col-md-4 text-start">
          <p><strong>興趣：</strong><?= htmlspecialchars($row['Schol_Apti']); ?></p>
          <p><strong>能力：</strong><?= htmlspecialchars($row['Talent']); ?></p>
          <p><strong>身份：</strong><?= htmlspecialchars($row['ID']); ?></p>
          <p><strong>計畫類別：</strong><?= htmlspecialchars($row['Plan']); ?></p>
          <p><strong>連結：</strong><a href="<?= htmlspecialchars($row['link']); ?>" target="_blank"><?= htmlspecialchars($row['link']); ?></a></p>
        </div>
        <div class="col-md-4 text-start">
          <p><strong>近五年錄取趨勢：</strong></p>
          <canvas id="chart-<?= $row['Sch_num']; ?>" width="300" height="200"></canvas>
        </div>
      </div>
    </div>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?>
<p>沒有找到相關結果。</p>
<?php endif; ?>

<!-- 圖表腳本 -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  <?php foreach ($results as $row): ?>
    new Chart(document.getElementById("chart-<?php echo $row['Sch_num']; ?>"), {
      type: "line",
      data: {
        labels: ["110", "111", "112", "113", "114"],
        datasets: [{
          label: "錄取人數",
          data: [
            <?php echo (int)($row["110"] ?? 0); ?>,
            <?php echo (int)($row["111"] ?? 0); ?>,
            <?php echo (int)($row["112"] ?? 0); ?>,
            <?php echo (int)($row["113"] ?? 0); ?>,
            <?php echo (int)($row["114"] ?? 0); ?>
          ],
          borderColor: "#007bff",
          backgroundColor: "rgba(0,123,255,0.2)",
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        }
      }
    });
  <?php endforeach; ?>
});
</script>

</div></section>
</main>

<!-- footer、scripts、preloader 略保留不變 -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
