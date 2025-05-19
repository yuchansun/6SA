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
    /* Apply dark background and white text color to card headers */
    .card-header {
      background-color: #1e4356; /* Dark background */
      color: white; /* White text */
    }

    /* Chart and title styling */
    #trendChart {
      background-color: rgb(255, 255, 255);
      color: rgb(9, 37, 75);
    }

    .card h4 {
      color: rgb(9, 37, 75);
      text-align: center;
    }
  </style>
</head>

<body class="about-page">

<?php
$sch_num = $_GET['sch_num'] ?? '';

if (empty($sch_num)) {
    die("查無學校資訊");
}

$conn = new mysqli("localhost", "root", "", "sa-6");
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// 取得 sch_description 的基本資料
$sql1 = "SELECT * FROM sch_description WHERE Sch_num = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $sch_num);
$stmt1->execute();
$result1 = $stmt1->get_result();
$schoolData = $result1->fetch_assoc();

if (!$schoolData) {
    die("查無學校資料");
}

// 取得正規化歷年人數資料
$sql2 = "SELECT year, student_count FROM admi_thro_years_normalized WHERE sch_num = ? ORDER BY year ASC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $sch_num);
$stmt2->execute();
$result2 = $stmt2->get_result();

$studentCounts = [];
while ($row = $result2->fetch_assoc()) {
    $studentCounts[$row['year']] = $row['student_count'];
}

$conn->close();
?>

<main class="main">
  <div class="page-title dark-background">
    <div class="container position-relative">
      <h1><?= htmlspecialchars($schoolData['School_Name']) . ' - ' . htmlspecialchars($schoolData['Department']) ?> 詳細介紹</h1>
    </div>
  </div>

  <div class="container mt-5">
    <div class="row">
      <!-- 學校基本資料卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">學校資訊</h5>
          </div>
          <div class="card-body">
            <p><strong>學校名稱：</strong><?= htmlspecialchars($schoolData['School_Name']) ?></p>
            <p><strong>公私立：</strong><?= htmlspecialchars($schoolData['p_type']) ?></p>
            <p><strong>科系：</strong><?= htmlspecialchars($schoolData['Department']) ?></p>
            <p><strong>名額：</strong><?= htmlspecialchars($schoolData['Quota']) ?></p>
            <p><strong>地區：</strong><?= htmlspecialchars($schoolData['Region']) ?></p>
          </div>
        </div>
      </div>

      <!-- 報考資訊卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">報考資訊</h5>
          </div>
          <div class="card-body">
            <p><strong>考試項目：</strong><?= htmlspecialchars($schoolData['Exam_Item']) ?></p>
            <p><strong>考試時間：</strong><?= htmlspecialchars($schoolData['exam_date']) ?></p>
            <p><strong>地址：</strong><?= htmlspecialchars($schoolData['address']) ?></p>
            <p><strong>電話：</strong><?= htmlspecialchars($schoolData['Contact']) ?></p>
            <p><strong>官方連結：</strong><a href="<?= htmlspecialchars($schoolData['link']) ?>" target="_blank"><?= htmlspecialchars($schoolData['link']) ?></a></p>
          </div>
        </div>
      </div>

      <!-- 補充資料卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">補充</h5>
          </div>
          <div class="card-body">
            <p><strong>學群：</strong><?= htmlspecialchars($schoolData['Disc_Cluster']) ?></p>
            <p><strong>能力：</strong><?= htmlspecialchars($schoolData['Talent']) ?></p>
            <p><strong>備註：</strong><?= htmlspecialchars($schoolData['note']) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- 篩選條件與圖表 -->
    <div class="row mt-4 mb-5">
      <div class="col-md-6 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">篩選條件</h5>
          </div>
          <div class="card-body">
            <p><strong>資格：</strong><?= htmlspecialchars($schoolData['requirement']) ?></p>
          </div>
        </div>
      </div>

      <div class="col-md-6 d-flex align-items-stretch">
        <div class="card w-100">
          <h4 class="mt-4 text-center">近五年錄取人數趨勢</h4>
          <div class="d-flex justify-content-center">
            <canvas id="trendChart" style="max-width: 100%; height: 100%;"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  const labels = ["110", "111", "112", "113", "114"];
  const data = [
    <?php
      foreach (["110", "111", "112", "113", "114"] as $year) {
          echo isset($studentCounts[$year]) ? (int)$studentCounts[$year] : 0;
          echo ",";
      }
    ?>
  ];

  new Chart(document.getElementById("trendChart"), {
    type: "line",
    data: {
      labels: labels,
      datasets: [{
        label: "錄取人數",
        data: data,
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
</script>




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
