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

// 資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// 取得搜尋 & 篩選參數
$filters = [
    "q" => $_GET['q'] ?? "",
    "region" => $_GET['region'] ?? "",
    "department" => $_GET['department'] ?? "",
    "plan" => $_GET['plan'] ?? "",
    "schol_apti" => $_GET['schol_apti'] ?? "",
    "talent" => $_GET['talent'] ?? "",
    "ID" => $_GET['ID'] ?? "",
    "school_name" => $_GET['school_name'] ?? "",
    "disc_cluster" => $_GET['disc_cluster'] ?? ""
];


$keywordMapping = [
    "清大" => "清華大學",
    "台大" => "台灣大學",
    "交大" => "交通大學",
    "成大" => "成功大學",
];


$sql = "SELECT sd.*, aty.110, aty.111, aty.112, aty.113, aty.114 
        FROM sch_description sd 
        LEFT JOIN admi_thro_years aty ON sd.Sch_num = aty.sch_num 
        WHERE 1=1";

$params = [];
$types = "";

// 處理關鍵字搜尋
if (!empty($filters["q"])) {
    $searchColumns = ["Sch_num", "School_Name", "Department", "Region", "Disc_Cluster", "Schol_Apti", "Talent", "ID", "Plan", "Quota", "Contact", "link"];
    $searchConditions = [];

    $searchTerms = preg_split('/\s+/', trim($filters["q"]));
    $expandedTerms = [];

    foreach ($searchTerms as $term) {
        $expandedTerms[] = $term;
        if (isset($keywordMapping[$term])) {
            $expandedTerms[] = $keywordMapping[$term];
        }
    }

    foreach ($expandedTerms as $term) {
        foreach ($searchColumns as $col) {
            $searchConditions[] = "sd.$col LIKE ?";
            $params[] = "%" . $term . "%";
            $types .= "s";
        }
    }

    if (!empty($searchConditions)) {
        $sql .= " AND (" . implode(" OR ", $searchConditions) . ")";
    }
}

// 處理其他篩選條件
foreach ($filters as $key => $value) {
    if ($key !== "q" && !empty($value)) {
        $sql .= " AND $key = ?";
        $params[] = $value;
        $types .= "s";
    }
}

// 預備 SQL 語句
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL 錯誤: " . $conn->error);
}

// 綁定參數並執行
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$results = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>



<style> 
  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
  }



  .search-button, .clear-button {
    background-color: color-mix(in srgb, var(--default-color), transparent 94%);
    border-radius: 20px;
    padding: 8px;
  }


  .search-button:hover ,
  .clear-button:hover {
    opacity: 0.8;
    background-color:var(--accent-color);
     color:white;
  }

  .search-input {
    border-radius: 20px;
    padding: 8px;
    border: none;
    background-color: color-mix(in srgb, var(--default-color), transparent 94%);
  }

  select, button {
    border-radius: 20px;
    padding: 10px 20px;
    border: 0;
    background-color: color-mix(in srgb, var(--default-color), transparent 94%);
  }


</style>


<div class="filter-container">
<form method="GET" action="" class="filter-form">
    <select name="region">
        <option value="">選擇地區</option>
        <option value="台北" <?php if ($filters["region"] == "台北") echo "selected"; ?>>台北</option>
        <option value="中部" <?php if ($filters["region"] == "中部") echo "selected"; ?>>中部</option>
        <option value="南部" <?php if ($filters["region"] == "南部") echo "selected"; ?>>南部</option>
    </select>

    <select name="school_name">
        <option value="">選擇學校</option>
        <option value="輔仁大學" <?php if ($filters["school_name"] == "輔仁大學") echo "selected"; ?>>輔仁大學</option>
        <option value="台灣大學" <?php if ($filters["school_name"] == "台灣大學") echo "selected"; ?>>台灣大學</option>
    </select>

    <select name="department">
        <option value="">選擇科系</option>
        <option value="資訊管理" <?php if ($filters["department"] == "資訊管理") echo "selected"; ?>>資訊管理</option>
        <option value="電機工程" <?php if ($filters["department"] == "電機工程") echo "selected"; ?>>電機工程</option>
    </select>

    <select name="disc_cluster">
        <option value="">選擇學群</option>
        <option value="工程" <?php if ($filters["disc_cluster"] == "工程") echo "selected"; ?>>工程</option>
        <option value="商業" <?php if ($filters["disc_cluster"] == "商業") echo "selected"; ?>>商業</option>
        <option value="科技學群" <?php if ($filters["disc_cluster"] == "科技學群") echo "selected"; ?>>科技學群</option>
    </select>

    <select name="plan">
        <option value="">選擇計畫類別</option>
        <option value="特殊選才" <?php if ($filters["plan"] == "特殊選才") echo "selected"; ?>>特殊選才</option>
        <option value="願景計畫" <?php if ($filters["plan"] == "願景計畫") echo "selected"; ?>>願景計畫</option>
        <option value="資安外加" <?php if ($filters["plan"] == "資安外加") echo "selected"; ?>>資安外加</option>
    </select>

    <select name="ID">
        <option value="">選擇身份</option>
        <option value="學生" <?php if ($filters["ID"] == "學生") echo "selected"; ?>>學生</option>
        <option value="上班族" <?php if ($filters["ID"] == "上班族") echo "selected"; ?>>上班族</option>
    </select>

    <select name="schol_apti">
        <option value="">選擇興趣</option>
        <option value="數學" <?php if ($filters["schol_apti"] == "數學") echo "selected"; ?>>數學</option>
        <option value="文學" <?php if ($filters["schol_apti"] == "文學") echo "selected"; ?>>文學</option>
    </select>

    <select name="talent">
        <option value="">選擇能力</option>
        <option value="程式設計" <?php if ($filters["talent"] == "程式設計") echo "selected"; ?>>程式設計</option>
        <option value="資料分析" <?php if ($filters["talent"] == "資料分析") echo "selected"; ?>>資料分析</option>
    </select>

    <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>" 
           placeholder="輸入關鍵字..." class="search-input">
    
    <!-- 搜尋按鈕 -->
    <button type="submit" class="search-button">搜尋 <i class="bi bi-search"></i></button>
    
</form>
</div>
      <div style="display: flex; justify-content: flex-end;">
        <form method="GET" >
        <button type="submit" class="clear-button">重置 <i class="bi bi-arrow-clockwise"></i></button>
        </form>
       </div>

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
          <button class="btn  btn-sm" style=" background-color: var(--accent-color); color:white;" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $row['Sch_num']; ?>" aria-expanded="false" aria-controls="details-<?php echo $row['Sch_num']; ?>">
            詳細介紹
          </button>
        </td>
        <td>
        <button class="favorite-btn" style="background-color:none" data-sch-num="<?php echo $row['Sch_num']; ?>" onclick="toggleStar(this)">
        <i class="bi bi-star"></i> <!-- 初始為空心星星 -->
</button>

<script>
  function toggleStar(button) {
    var star = button.querySelector('i');
    if (star.classList.contains('bi-star')) {
      star.classList.remove('bi-star');
      star.classList.add('bi-star-fill');
      star.style.color = '#FFCC00'; 
    } else {
      star.classList.remove('bi-star-fill');
      star.classList.add('bi-star');
      star.style.color = 'black'; 
    }
  }
</script>


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

  <footer id="footer" class="footer dark-background">

    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-6">
            <h4>Join Our Newsletter</h4>
            <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
            <form action="forms/newsletter.php" method="post" class="php-email-form">
              <div class="newsletter-form"><input type="email" name="email"><input type="submit" value="Subscribe"></div>
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Your subscription request has been sent. Thank you!</div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="d-flex align-items-center">
            <span class="sitename">Moderna</span>
          </a>
          <div class="footer-contact pt-3">
            <p>A108 Adam Street</p>
            <p>New York, NY 535022</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+1 5589 55488 55</span></p>
            <p><strong>Email:</strong> <span>info@example.com</span></p>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">About us</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Services</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Terms of service</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Services</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Web Design</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Web Development</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Product Management</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Marketing</a></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12">
          <h4>Follow Us</h4>
          <p>Cras fermentum odio eu feugiat lide par naso tierra videa magna derita valies</p>
          <div class="social-links d-flex">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Moderna</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

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