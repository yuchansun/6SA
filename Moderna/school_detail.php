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

$sql = "SELECT sd.*, aty.110, aty.111, aty.112, aty.113, aty.114 
        FROM sch_description sd 
        LEFT JOIN admi_thro_years aty ON sd.Sch_num = aty.sch_num 
        WHERE sd.Sch_num = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$conn->close();
?>

<main class="main">
  <div class="page-title dark-background">
    <div class="container position-relative">
      <?php if ($data): ?>
        <h1><?= htmlspecialchars($data['School_Name']) . ' - ' . htmlspecialchars($data['Department']) ?> 詳細介紹</h1>
      <?php else: ?>
        <h1>學校詳細介紹</h1>
      <?php endif; ?>
    </div>
  </div>
  <!-- 這裡是其他頁面內容 -->
</main>


<style>
  
  /* Apply dark background and white text color to card headers */
  .card-header {
    background-color:rgb(10, 42, 75); /* Dark background */
    color: white; /* White text */
  }
  <style>
  /* Apply dark background and white text color to card headers */
  .card-header {
    background-color: #343a40; /* Dark background */
    color: white; /* White text */
  }

  /* Apply dark background and white color to the chart title */
  #trendChart {
    background-color:rgb(255, 255, 255);
    color:rgb(9, 37, 75);
  }

  .card h4 {
    color: rgb(9, 37, 75); /* Ensure the title of the chart is white */
    text-align: center;
  }

</style>


<div class="container mt-5">
  <?php if ($data): ?>
    <div class="row">
      <!-- 學校基本資料卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">學校資訊</h5>
          </div>
          <div class="card-body">
            <p><strong>學校名稱：</strong><?= htmlspecialchars($data['School_Name']) ?></p>
            <p><strong>公私立：</strong><?= htmlspecialchars($data['p_type']) ?></p>
            <p><strong>科系：</strong><?= htmlspecialchars($data['Department']) ?></p>
            <p><strong>名額：</strong><?= htmlspecialchars($data['Quota']) ?></p>
            <p><strong>地區：</strong><?= htmlspecialchars($data['Region']) ?></p>
          </div>
        </div>
      </div>

      <!-- 名額與聯繫方式卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">報考資訊</h5>
          </div>
          <div class="card-body">
            <p><strong>考試項目：</strong><?= htmlspecialchars($data['Exam_Item']) ?></p>
            <p><strong>考試項目：</strong><?= htmlspecialchars($data['exam_date']) ?></p>  
            <p><strong>地址：</strong><?= htmlspecialchars($data['address']) ?></p>
            <p><strong>電話：</strong><?= htmlspecialchars($data['Contact']) ?></p>
            <p><strong>官方連結：</strong><a href="<?= htmlspecialchars($data['link']) ?>" target="_blank"><?= htmlspecialchars($data['link']) ?></a></p>
          </div>
        </div>
      </div>

      <!-- 興趣、能力與身份卡片 -->
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">興趣、能力與身份</h5>
          </div>
          <div class="card-body">
            <p><strong>學類：</strong><?= htmlspecialchars($data['Disc_Cluster']) ?></p>
            <p><strong>興趣：</strong><?= htmlspecialchars($data['requirement']) ?></p>
            <p><strong>能力：</strong><?= htmlspecialchars($data['Talent']) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- 備註與歷年區域圖表並排顯示 -->
    <div class="row mt-4 mb-5"> <!-- Added mb-4 here for spacing -->
      <!-- 備註卡片 -->
      <div class="col-md-6 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-header">
            <h5 class="card-title">備註</h5>
          </div>
          <div class="card-body">
            <p><strong>備註：</strong><?= htmlspecialchars($data['note']) ?></p>
          </div>
        </div>
      </div>

      <!-- 錄取人數趨勢圖表 -->
      <div class="col-md-6 d-flex align-items-stretch">
        <div class="card w-100">
          <h4 class="mt-4 text-center">近五年錄取人數趨勢</h4>
          <div class="d-flex justify-content-center">
            <canvas id="trendChart" style="max-width: 100%; height: 100%;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <script>
      new Chart(document.getElementById("trendChart"), {
        type: "line",
        data: {
          labels: ["110", "111", "112", "113", "114"],
          datasets: [{
            label: "錄取人數",
            data: [
              <?= (int)($data['110'] ?? 0) ?>,
              <?= (int)($data['111'] ?? 0) ?>,
              <?= (int)($data['112'] ?? 0) ?>,
              <?= (int)($data['113'] ?? 0) ?>,
              <?= (int)($data['114'] ?? 0) ?>
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
    </script>

  <?php else: ?>
    <p>查無此學校資料。</p>
  <?php endif; ?>
</div>



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
