<?php
session_start();
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
// 抓取下拉選單的項目
function getDistinctOptions($conn, $column, $table = "sch_description") {
  $sql = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` IS NOT NULL AND `$column` <> ''";
  $result = $conn->query($sql);
  $options = [];
  if ($result) {
      while ($row = $result->fetch_assoc()) {
          $options[] = $row[$column];
      }
  }
  return $options;
}

$regionOptions = getDistinctOptions($conn, 'Region');
$schoolOptions = getDistinctOptions($conn, 'School_Name');
$departmentOptions = getDistinctOptions($conn, 'Department');
$discClusterOptions = getDistinctOptions($conn, 'Disc_Cluster');
$planOptions = getDistinctOptions($conn, 'Plan');
$idOptions = getDistinctOptions($conn, 'ID');
$talentOptions = getDistinctOptions($conn, 'Talent');


// 取得搜尋 & 篩選參數
$filters = [
  "q" => $_GET['q'] ?? "",
  "region" => $_GET['region'] ?? "",
  "department" => $_GET['department'] ?? "",
  "plan" => $_GET['plan'] ?? "",
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
    $searchColumns = ["Sch_num", "School_Name", "Department", "Region", "Disc_Cluster",  "Talent", "ID", "Plan", "Quota", "Contact", "link"];
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




<div class="filter-container">
<form method="GET" action="" class="filter-form">
    <select name="region">
        <option value="">選擇地區</option>
        <?php foreach ($regionOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["region"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="school_name">
        <option value="">選擇學校</option>
        <?php foreach ($schoolOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["school_name"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="department">
        <option value="">選擇科系</option>
        <?php foreach ($departmentOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["department"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="disc_cluster">
        <option value="">選擇學群</option>
        <?php foreach ($discClusterOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["disc_cluster"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="plan">
        <option value="">選擇計畫類別</option>
        <?php foreach ($planOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["plan"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="ID">
        <option value="">選擇身份</option>
        <?php foreach ($idOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["ID"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    
    <select name="talent">
        <option value="">選擇能力</option>
        <?php foreach ($talentOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["talent"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="輸入關鍵字..." class="search-input">
    
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
  <a href="school_detail.php?sch_num=<?= urlencode($row['Sch_num']) ?>" class="btn btn-sm" style="background-color: var(--accent-color); color:white;">
  詳細介紹
</a>

        </td>
        <td>
    <!-- 收藏按鈕 -->
<button class="favorite-btn" style="background-color:none"
  data-sch-num="<?php echo $row['Sch_num']; ?>"
  onclick="toggleStar(this)">
  <i class="bi bi-star"></i>
</button>

<!-- 中央通知 -->
<div id="notification" style="
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #444;
  padding: 16px 28px;
  border-radius: 10px;
  display: none;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  transition: all 0.3s ease;
">
  <a href="favorite.php" id="notification-link" style="
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    display: block;
  ">
    已加入收藏！點我查看
  </a>
</div>


<script>
function toggleStar(button) {
  const star = button.querySelector('i');
  const schNum = button.getAttribute('data-sch-num');
  let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

  const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
  const user_id = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

  if (star.classList.contains('bi-star')) {
    // 加入收藏
    star.classList.remove('bi-star');
    star.classList.add('bi-star-fill');
    star.style.color = '#FFCC00';

    if (!favorites.includes(schNum)) {
      favorites.push(schNum);
      localStorage.setItem('favorites', JSON.stringify(favorites));
      showNotification("已加入收藏！點我查看", "favorite.php");

      if (isLoggedIn) {
        // 傳送加入收藏
        fetch('add_favorite.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'sch_num=' + encodeURIComponent(schNum) + '&user_id=' + encodeURIComponent(user_id),
          credentials: 'include'
        })
        .then(response => response.text())
        .then(data => console.log(data));

        // ✅ 同時新增到 user_todos
        fetch('add_user_todos.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'sch_num=' + encodeURIComponent(schNum),
          credentials: 'include'
        })
        .then(response => response.text())
        .then(data => console.log('加入 user_todos 結果：', data));
      }
    }

  } else {
    // 取消收藏
    star.classList.remove('bi-star-fill');
    star.classList.add('bi-star');
    star.style.color = 'black';

    favorites = favorites.filter(fav => fav !== schNum);
    localStorage.setItem('favorites', JSON.stringify(favorites));
    showNotification("已解除收藏", "#", false);

    if (isLoggedIn) {
      fetch('remove_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'sch_num=' + encodeURIComponent(schNum),
        credentials: 'include'
      });
    }
  }
}

window.onload = function () {
  const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

  if (isLoggedIn) {
    // 取得收藏資料
    fetch('get_favorites.php', {
      credentials: 'include'
    })
    .then(response => response.json())
    .then(favoritesFromDB => {
      const buttons = document.querySelectorAll('.favorite-btn');
      buttons.forEach(button => {
        const schNum = button.getAttribute('data-sch-num');
        const star = button.querySelector('i');
        if (favoritesFromDB.includes(schNum)) {
          star.classList.remove('bi-star');
          star.classList.add('bi-star-fill');
          star.style.color = '#FFCC00';
        }
      });

      // 將所有收藏加入 user_todos（保險機制）
      favoritesFromDB.forEach(schNum => {
        fetch('add_user_todos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'sch_num=' + encodeURIComponent(schNum),
          credentials: 'include'
        })
        .then(response => response.text())
        .then(data => console.log('加入 user_todos 結果：', data));
      });

      localStorage.setItem('favorites', JSON.stringify(favoritesFromDB));
    });

  } else {
    // 未登入，從 localStorage 顯示收藏
    const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    const buttons = document.querySelectorAll('.favorite-btn');
    buttons.forEach(button => {
      const schNum = button.getAttribute('data-sch-num');
      const star = button.querySelector('i');
      if (favorites.includes(schNum)) {
        star.classList.remove('bi-star');
        star.classList.add('bi-star-fill');
        star.style.color = '#FFCC00';
      }
    });
  }
};

function showNotification(message, link, clickable = true) {
  const notification = document.getElementById('notification');
  const linkEl = document.getElementById('notification-link');

  linkEl.textContent = message;
  linkEl.href = link;

  if (clickable) {
    linkEl.style.pointerEvents = "auto";
    linkEl.style.opacity = "1";
    linkEl.style.cursor = "pointer";
  } else {
    linkEl.style.pointerEvents = "none";
    linkEl.style.opacity = "0.7";
    linkEl.style.cursor = "default";
  }

  notification.style.display = 'block';

  setTimeout(() => {
    notification.style.display = 'none';
  }, 3000);
}
</script>



</tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?>
<p>沒有找到相關結果。</p>
<?php endif; ?>


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

</div></section>
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