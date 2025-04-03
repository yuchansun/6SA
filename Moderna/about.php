<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>About - Moderna Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Moderna
  * Template URL: https://bootstrapmade.com/free-bootstrap-template-corporate-moderna/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="about-page">


  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1>校系簡章</h1>
        <p>基本的特殊選才資訊查詢功能，讓使用者可以透過關鍵字與篩選條件找到適合的學校與學程。</p>
        
      </div>
    </div><!-- End Page Title -->

    <!-- About Section -->
    <section id="about" class="about section">
      <div class="container">
       
        <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sa-6"; // 資料庫名稱

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
    "identity" => $_GET['ID'] ?? "",
    "school_name" => $_GET['school_name'] ?? "",
    "disc_cluster" => $_GET['disc_cluster'] ?? ""
];

// 構建 SQL 查詢
$sql = "SELECT * FROM sch_description WHERE 1=1";
$params = [];
$types = "";

// 處理關鍵字搜尋
if (!empty($filters["q"])) {
    $searchColumns = ["Sch_num", "School_Name", "Department", "Region", "Disc_Cluster", "Schol_Apti", "Talent", "ID", "Plan", "Quota", "Contact", "link"];
    $searchConditions = array_map(fn($col) => "$col LIKE ?", $searchColumns);
    $sql .= " AND (" . implode(" OR ", $searchConditions) . ")";
    foreach ($searchColumns as $col) {
        $params[] = "%" . $filters["q"] . "%";
        $types .= "s";
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


<div class="search-widget widget-item">
    <form method="GET" action="">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>" placeholder="輸入關鍵字...">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>
</div><!--/搜尋小工具-->

<!-- 篩選條件 -->
<div class="filter-row" style="text-align: center;">
    <form method="GET" action="" style="display: inline-block;">
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
            <option value="短期計畫" <?php if ($filters["plan"] == "短期計畫") echo "selected"; ?>>短期計畫</option>
            <option value="長期計畫" <?php if ($filters["plan"] == "長期計畫") echo "selected"; ?>>長期計畫</option>
        </select>

        <select name="identity">
            <option value="">選擇身份</option>
            <option value="學生" <?php if ($filters["identity"] == "學生") echo "selected"; ?>>學生</option>
            <option value="上班族" <?php if ($filters["identity"] == "上班族") echo "selected"; ?>>上班族</option>
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

        <div class="button-group" style="margin-top: 20px;">
            <button type="submit">篩選</button></form>
            <form method="GET" style="display: inline-block;">
                <button type="submit">清除篩選</button>
            </form>
        </div>
    
</div>

<!-- 顯示搜尋結果 -->
<?php if (!empty($results)): ?>
  <table class="table table-striped table-hover align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>學校</th>
        <th>科系</th>
        <th>名額</th>
        <th>詳細資料</th>
        <th>收藏</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['School_Name']); ?></td>
        <td><?php echo htmlspecialchars($row['Department']); ?></td>
        <td><?php echo htmlspecialchars($row['Quota']); ?></td>
        <td>
          <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $row['Sch_num']; ?>" aria-expanded="false" aria-controls="details-<?php echo $row['Sch_num']; ?>">
            詳細介紹
          </button>
        </td>
        <td>
          <button class="btn btn-outline-danger btn-sm favorite-btn" data-sch-num="<?php echo $row['Sch_num']; ?>">
            <i class="bi bi-heart"></i>
          </button>
        </td>
      </tr>
      <tr class="collapse" id="details-<?php echo $row['Sch_num']; ?>">
        <td colspan="5">
          <div class="card card-body">
            <div class="row">
              <!-- 左邊區塊 -->
              <div class="col-md-4 text-start">
                <p><strong>學校：</strong><?php echo htmlspecialchars($row['School_Name']); ?></p>
                <p><strong>科系：</strong><?php echo htmlspecialchars($row['Department']); ?></p>
                <p><strong>名額：</strong><?php echo htmlspecialchars($row['Quota']); ?></p>
                <p><strong>電話：</strong><?php echo htmlspecialchars($row['Contact']); ?></p>
              </div>
              <!-- 中間區塊 -->
              <div class="col-md-4 text-start">
                <p><strong>興趣：</strong><?php echo htmlspecialchars($row['Schol_Apti']); ?></p>
                <p><strong>能力：</strong><?php echo htmlspecialchars($row['Talent']); ?></p>
                <p><strong>身份：</strong><?php echo htmlspecialchars($row['ID']); ?></p>
                <p><strong>計畫類別：</strong><?php echo htmlspecialchars($row['Plan']); ?></p>
                <p><strong>連結：</strong><a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank"><?php echo htmlspecialchars($row['link']); ?></a></p>
              </div>
              <!-- 右邊區塊 -->
              <div class="col-md-4 text-start">
                <!-- 預留空間，方便後續加入其他內容 -->
              </div>
            </div>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const favoriteButtons = document.querySelectorAll('.favorite-btn');
      favoriteButtons.forEach(button => {
        button.addEventListener('click', function () {
          const icon = this.querySelector('i');
          if (icon.classList.contains('bi-heart')) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            this.classList.remove('btn-outline-danger');
            this.classList.add('btn-danger');
          } else {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            this.classList.remove('btn-danger');
            this.classList.add('btn-outline-danger');
          }
        });
      });
    });
  </script>
<?php else: ?>
    <p>沒有找到相關結果。</p>
<?php endif; ?>

<style>
  select, button {
    border-radius: 20px;
    padding: 10px 20px;
    border: 0;
    background-color: var(--accent-color);
    color: var(--contrast-color);
  }


  select {
    flex: 1 1 200px; /* 設置寬度並允許自動調整 */
    min-width: 150px; /* 設定最小寬度 */
  }

  button {
    flex: 0 1 100px; /* 設定按鈕寬度 */
    cursor: pointer; /* 設定游標為手形，提示可以點擊 */
    background-color: var(--default-color);
    color: white; /* 設定文字顏色 */
    border: none; /* 去掉按鈕邊框 */
  }

  button:hover {
    background-color: #0056b3; /* 按鈕 hover 時的背景顏色 */
  }
</style>
       
    

    

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