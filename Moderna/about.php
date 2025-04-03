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
        <p>Esse dolorum voluptatum ullam est sint nemo et est ipsa porro placeat quibusdam quia assumenda numquam molestias.</p>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.html">Home</a></li>
            <li class="current">校系簡章</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- About Section -->
    <section id="about" class="about section">
      <div class="container">
       
        <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sa_6"; // 資料庫名稱

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
    "identity" => $_GET['identity'] ?? "",
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

<!-- 搜尋與篩選表單 -->
<form method="GET" class="form-container">
    <input type="text" name="q" value="<?php echo htmlspecialchars($filters["q"]); ?>" placeholder="輸入關鍵字...">
    <button type="submit">搜尋</button>

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

    <button type="submit">篩選</button>
</form>

<!-- 清除篩選按鈕 -->
<form method="GET">
    <button type="submit">清除篩選</button>
</form>

<!-- 顯示搜尋結果 -->
<?php if (!empty($results)): ?>
  <table class="table table-striped table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>學校</th>
                <th>科系</th>
                <th>學群</th>
                <th>計畫類別</th>
                <th>身份</th>
                <th>興趣</th>
                <th>能力</th>
                <th>名額</th>
                <th>連絡電話</th>
                <th>詳細介紹</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['School_Name']); ?></td>
                <td><?php echo htmlspecialchars($row['Department']); ?></td>
                <td><?php echo htmlspecialchars($row['Disc_Cluster']); ?></td>
                <td><?php echo htmlspecialchars($row['Plan']); ?></td>
                <td><?php echo htmlspecialchars($row['ID']); ?></td>
                <td><?php echo htmlspecialchars($row['Schol_Apti']); ?></td>
                <td><?php echo htmlspecialchars($row['Talent']); ?></td>
                <td><?php echo htmlspecialchars($row['Quota']); ?></td>
                <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                <td><a href="<?php echo htmlspecialchars($row['link']); ?>">詳細介紹</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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

  .form-container {
    display: flex;
    flex-wrap: wrap; /* 讓元素換行 */
    gap: 10px; /* 設置元素間距 */
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
       
    </section><!-- /About Section -->

    <!-- Stats Section -->
    <section id="stats" class="stats section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="1" class="purecounter"></span>
              <p>Clients</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="1" class="purecounter"></span>
              <p>Projects</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="1453" data-purecounter-duration="1" class="purecounter"></span>
              <p>Hours Of Support</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="32" data-purecounter-duration="1" class="purecounter"></span>
              <p>Workers</p>
            </div>
          </div><!-- End Stats Item -->

        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Clients Section -->
    <section id="clients" class="clients section">

      <div class="container">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 2,
                  "spaceBetween": 40
                },
                "480": {
                  "slidesPerView": 3,
                  "spaceBetween": 60
                },
                "640": {
                  "slidesPerView": 4,
                  "spaceBetween": 80
                },
                "992": {
                  "slidesPerView": 6,
                  "spaceBetween": 120
                }
              }
            }
          </script>
          <div class="swiper-wrapper align-items-center">
            <div class="swiper-slide"><img src="assets/img/clients/client-1.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-2.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-3.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-4.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-5.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-6.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-7.png" class="img-fluid" alt=""></div>
            <div class="swiper-slide"><img src="assets/img/clients/client-8.png" class="img-fluid" alt=""></div>
          </div>
        </div>

      </div>

    </section><!-- /Clients Section -->

    <!-- Skills Section -->
    <section id="skills" class="skills section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Our Skills</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row skills-content skills-animation">

          <div class="col-lg-6">

            <div class="progress">
              <span class="skill"><span>HTML</span> <i class="val">100%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

            <div class="progress">
              <span class="skill"><span>CSS</span> <i class="val">90%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

            <div class="progress">
              <span class="skill"><span>JavaScript</span> <i class="val">75%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

          </div>

          <div class="col-lg-6">

            <div class="progress">
              <span class="skill"><span>PHP</span> <i class="val">80%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

            <div class="progress">
              <span class="skill"><span>WordPress/CMS</span> <i class="val">90%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

            <div class="progress">
              <span class="skill"><span>Photoshop</span> <i class="val">55%</i></span>
              <div class="progress-bar-wrap">
                <div class="progress-bar" role="progressbar" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div><!-- End Skills Item -->

          </div>

        </div>

      </div>

    </section><!-- /Skills Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Testimonials</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                <h3>Saul Goodman</h3>
                <h4>Ceo &amp; Founder</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                <h3>Sara Wilsson</h3>
                <h4>Designer</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                <h3>Jena Karlis</h3>
                <h4>Store Owner</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
                <h3>Matt Brandon</h3>
                <h4>Freelancer</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
                <h3>John Larson</h3>
                <h4>Entrepreneur</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

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