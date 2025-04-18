<?php include('header.php'); ?>
<?php if (isset($_SESSION['user'])): ?>
<script>
  if (!localStorage.getItem('userLoggedIn')) {
    localStorage.setItem('userLoggedIn', 'true');
    localStorage.setItem('userId', '<?= $_SESSION['user'] ?>');
  }
</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Index - Moderna Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <!--  -->
  
  <!-- =======================================================
  * Template Name: Moderna
  * Template URL: https://bootstrapmade.com/free-bootstrap-template-corporate-moderna/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

<?php
// 資料庫連線設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sa-6"; // 替換為你的資料庫名稱
// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 從 latest_news 資料表中抓取資料
$sql = "SELECT title, content, link FROM latest_news LIMIT 3";
$result = $conn->query($sql);
?>

  <main class="main">

    <!-- 最新消息 -->
    <section id="hero" class="hero section dark-background">

      <div id="hero-carousel" class="carousel carousel-fade" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="false">

      <div class="carousel-inner">
        <?php
        if ($result->num_rows > 0) {
            $isActive = true; // 用於設定第一個項目為 active
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                  <div class="carousel-container">
                    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                    <p><?php echo htmlspecialchars($row['content']); ?></p>
                    <a href="<?php echo htmlspecialchars($row['link']); ?>" class="btn-get-started">連結網址</a>
                  </div>
                </div>
                <?php
                $isActive = false; // 之後的項目不再是 active
            }
        } else {
            ?>
            <p>No news available.</p>
            <?php
        }
        ?>
      </div>
      <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </a>
      <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
        <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </a>
      
      <div class="carousel-indicators ">
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>


    </div>
    </div>
    </section><!-- /最新消息 -->

    <div>
        <section>
        </section>
      </div>

    <!-- 特殊選才是什麼? -->
    <section id="about" class="about section light-background">

      <div class="container">

        <div class="row gy-4">
          <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="100">
            <img src="assets\img\螢幕擷取畫面 2025-04-15 234805.png" class="img-fluid" alt="">
            <a href="https://youtu.be/DX90XM9JJR0" class="glightbox pulsating-play-btn"></a>
          </div>
          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="200">
            <h3>特殊選才是什麼?</h3>
            <p>
            「特殊選才」是一種專為具有獨特才能、經歷或背景的學生設計的多元入學方式。與學測、指考等傳統的入學考試不同的是，特殊選才允許學生透過備審資料和面試申請入學，無須提供學測成績，讓各大學能根據自身的需求靈活選拔出較為「偏才」或「專才」的學生。</p>
            <ul>
              <li><i class="bi bi-check2-all"></i> <span>唯一不用大考的升學管道</span></li>
              <li><i class="bi bi-check2-all"></i> <span>特選名額逐年攀升</span></li>
              <li><i class="bi bi-check2-all"></i> <span>各校獨立招生</span></li>
              <li><i class="bi bi-check2-all"></i> <span>書審面視為關鍵標準</span></li>
            </ul>
            <p>
            以包容多元為核心精神的「特殊選才」，等於為那些在特定領域表現優秀的學生及來自弱勢背景的學子，提供了更多升學機會，也提升大學各校系在招生上的多樣性與創新能力。自 104 學年度首次試行以來，參與的學校逐年增加，招生名額至 113 學年度已擴增至 1,618 個，反映出各大學院校對擁有各類才能學生的重視。
            </p>
          </div>
        </div>

      </div>

    </section><!-- /特殊選才是什麼? -->



    <!-- 特殊選才總整理 -->
    <section id="features" class="features section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>特殊選才總整理</h2>
        <p></p>
      </div><!-- End Section Title -->

      <div class="container">
        <!-- 適合走特殊選才的六大類型 -->
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
            <img src="assets/img/features-1.svg" class="img-fluid" alt="">
          </div>
          <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
            <h3>適合走特殊選才的六大類型</h3>
            <p class="fst-italic">
            特殊選才的入學方式特別適合擁有以下特質的學生，這些特點有助於在申請過程中展現出與所選校系的契合度。
            </p>
            <ul>
              <li><i class="bi bi-check"></i><span> 特頂學科才能</span></li>
              <li><i class="bi bi-check"></i> <span>語言藝能專長</span></li>
              <li><i class="bi bi-check"></i><span> 創新領導能力</span></li>
              <li><i class="bi bi-check"></i> <span>優良行為表現</span></li>
              <li><i class="bi bi-check"></i><span> 逆境求學精神</span></li>
              <li><i class="bi bi-check"></i> <span>特殊教育背景</span></li>
            </ul>
          </div>
        </div><!-- Features Item -->
      
        <!-- 特殊選才申請流程 -->
        <div class="row gy-4 align-items-center features-item">
        <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out">
        <img src="assets/img/features-3.svg" class="img-fluid" alt="">
          </div>
          <div class="col-md-7 order-2 order-md-1" data-aos="fade-up">
            <h3>特殊選才申請流程</h3>
            <p>由於各特殊選才科系招考的確切時間不同，需留意報名、審查及放榜的具體時間，才能及早準備書面審查資料與面試練習。特殊選才的日程主要集中於每年的 10月~12月，以下是相關的大致時程，提供有意申請的學生作為參考。</p>
            <ul>
              <li><i class="bi bi-check"></i> <span>報名時間 : 每年10月 ~ 12月初</span></li>
              <li><i class="bi bi-check"></i><span>審查時間 : 11月 ~ 12月</span></li>
              <li><i class="bi bi-check"></i> <span>放榜時間 : 11月 ~ 12月，最遲至隔年1月</span>.</li>
            </ul>
          </div>
        </div><!-- Features Item -->
        <!-- 備審資料 -->
        <div class="row gy-4 align-items-center features-item">
        <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
        <img src="assets/img/features-4.svg" class="img-fluid" alt="">
          </div>
          <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
            <h3>備審資料</h3>
            <p class="fst-italic">
            關於特殊選才備審資料每間學校要求不盡相同，不過通常都包含以下幾種必備文件：
            </p>
            <ul>
              <li><i class="bi bi-check"></i> <span>高中學歷證明正本</span></li>
              <li><i class="bi bi-check"></i><span>高中歷年成績單正本</span></li>
              <li><i class="bi bi-check"></i> <span>高中教師或專業領域人士、教授推薦函</span>.</li>
              <li><i class="bi bi-check"></i> <span>其他有利審查的資料</span></li>

            </ul>
            <p>
            其他有利審查的資料用來證明自身獨特才能或潛力的相關證明文件，讓教授能從中看到你的潛力與專長，大致包括以下 5 個項目。
            個人簡歷、自傳、申請動機、讀書計畫、有利的佐證資料。	
            </p>
          </div>
        </div><!-- Features Item -->
        <!--  -->
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/features-2.svg" class="img-fluid" alt="">
          </div>
          <div class="col-md-7 order-2 order-md-1" data-aos="fade-up" data-aos-delay="200">
            <h3>討論區熱門文章</h3>
            <p class="fst-italic">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua.
            </p>
            <p>
              Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
              velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
              culpa qui officia deserunt mollit anim id est laborum
            </p>
          </div>
        </div>
        <!-- Features Item -->

      </div>

    </section><!-- /Features Section -->

  </main>

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