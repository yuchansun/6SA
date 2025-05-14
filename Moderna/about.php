<?php
session_start();
require_once 'db.php';

// 檢查是否為管理者
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT Roles FROM account WHERE User_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $isAdmin = ($row['Roles'] === '管理者');
            $_SESSION['user_role'] = $row['Roles'];
        }
    }
    $stmt->close();
}

$showAdminInterface = isset($_GET['admin']) && $isAdmin;

// 如果不是管理者但嘗試訪問管理介面，重定向到首頁
if (isset($_GET['admin']) && !$isAdmin) {
    header('Location: index.php');
    exit;
}
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
include 'db.php';

// 取得所有才能名稱
function getTalentOptions($conn) {
    $sql = "SELECT name FROM talents";
    $result = $conn->query($sql);
    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = $row['name'];
    }
    return $options;
}

// 取得單一欄位的唯一值（下拉選單用）
function getDistinctOptions($conn, $column, $table = "sch_description", $filterColumn = null, $filterValue = null) {
    $sql = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` IS NOT NULL AND `$column` <> ''";
    if ($filterColumn && $filterValue) {
        $sql .= " AND `$filterColumn` = ?";
    }
    $stmt = $conn->prepare($sql);
    if ($filterColumn && $filterValue) {
        $stmt->bind_param("s", $filterValue);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] = $row[$column];
    }
    return $options;
}

// 抓前端篩選值
$filters = [
    "q" => $_GET['q'] ?? "",
    "region" => $_GET['region'] ?? "",
    "department" => $_GET['department'] ?? "",
    "talent" => $_GET['talent'] ?? [],
    "school_name" => $_GET['school_name'] ?? "",
    "disc_cluster" => $_GET['disc_cluster'] ?? ""
];

// 取得下拉資料
$regionOptions = getDistinctOptions($conn, 'Region');
$schoolOptions = getDistinctOptions($conn, 'School_Name');
$departmentOptions = getDistinctOptions($conn, 'Department');
$discClusterOptions = getDistinctOptions($conn, 'Disc_Cluster');
$talentOptions = getTalentOptions($conn);

// 當有選學校時再過濾科系和學群
if (!empty($filters["school_name"])) {
    $departmentOptions = getDistinctOptions($conn, 'Department', "sch_description", "School_Name", $filters["school_name"]);
    $discClusterOptions = getDistinctOptions($conn, 'Disc_Cluster', "sch_description", "School_Name", $filters["school_name"]);
}

// SQL 組合開始
$params = [];
$types = "";

$sql = "SELECT sd.* FROM sch_description sd";

if (!empty($filters["talent"])) {
    // 有才能條件才 JOIN
    $sql .= "
        JOIN department_talents dt ON sd.Sch_num = dt.sch_num
        JOIN talents t ON dt.talent_id = t.id
    ";
}

$sql .= " WHERE sd.is_deleted = 0";

// 處理關鍵字搜尋
if (!empty($filters["q"])) {
    $columns = ["Sch_num", "School_Name", "Department", "Region", "Disc_Cluster", "Contact", "link"];
    $searchParts = [];
    $searchTerms = preg_split('/\s+/', trim($filters["q"]));
    foreach ($searchTerms as $term) {
        foreach ($columns as $col) {
            $searchParts[] = "sd.$col LIKE ?";
            $params[] = "%" . $term . "%";
            $types .= "s";
        }
    }
    if (!empty($searchParts)) {
        $sql .= " AND (" . implode(" OR ", $searchParts) . ")";
    }
}

// 處理才能（多選）
if (!empty($filters["talent"])) {
    $placeholders = implode(",", array_fill(0, count($filters["talent"]), "?"));
    $sql .= " AND t.name IN ($placeholders)";
    foreach ($filters["talent"] as $talent) {
        $params[] = $talent;
        $types .= "s";
    }

    // 確保同一個科系同時擁有這些才能
    $sql .= " GROUP BY sd.Sch_num HAVING COUNT(DISTINCT t.name) = ?";
    $params[] = count($filters["talent"]);
    $types .= "i";
}

// 處理其他欄位條件
foreach (["region", "department", "school_name", "disc_cluster"] as $key) {
    if (!empty($filters[$key])) {
        $sql .= " AND sd.$key = ?";
        $params[] = $filters[$key];
        $types .= "s";
    }
}

// 執行查詢
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL 錯誤: " . $conn->error);
}
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
    <?php if ($showAdminInterface): ?>
    <input type="hidden" name="admin" value="1">
    <?php endif; ?>
    <select name="region" id="region">
        <option value="">選擇地區</option>
        <?php foreach ($regionOptions as $option): ?>
            <option value="<?= htmlspecialchars($option) ?>" <?= ($filters["region"] == $option) ? "selected" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="school_name" id="school_name">
        <option value="">選擇學校</option>
    </select>

    <select name="disc_cluster" id="disc_cluster">
        <option value="">選擇學群</option>
    </select>

    <select name="department" id="department">
        <option value="">選擇科系</option>
    </select>
    <div class="filter-form">
    <!-- dropdown 按鈕 -->
    <button type="button" id="toggleTalentDropdown" class="select-button d-flex justify-content-between align-items-center">
    <span>
        <?= !empty($filters["talent"]) ? implode(", ", $filters["talent"]) : "選擇能力" ?>
    </span>
    <i class="bi bi-chevron-down" style="font-size: 10px;margin-left: 10px;"></i>

</button>


    <!-- dropdown 內容 -->
    <div id="talentDropdown" class="talent-dropdown" style="display:none;">
        <?php foreach ($talentOptions as $option): ?>
            <label>
                <input type="checkbox" name="talent[]" class="talent-option" value="<?= htmlspecialchars($option) ?>"
                    <?= in_array($option, $filters["talent"]) ? "checked" : "" ?>>
                <?= htmlspecialchars($option) ?>
            </label><br>
        <?php endforeach; ?>
    </div>
</div>


<style>
 .filter-form {
    position: relative;
    display: inline-block;
}
.talent-dropdown {
    display: none;
    margin-top: 5px;
    border: 1px solid #ccc;
    padding: 10px;
 
    position: absolute;
    top: 100%; /* 顯示在按鈕正下方 */
    left: 0;
    z-index: 1000;
    min-width: 200px; /* 設定最小寬度 */
    width: auto; /* 自動調整寬度 */
    box-sizing: border-box;
    max-height: 200px;
    overflow-y: auto;
    transition: opacity 0.2s ease, transform 0.2s ease;
    background-color:  #fff;
    border-radius: 0.375rem; /* Bootstrap 預設圓角 */
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); /* Bootstrap dropdown 陰影 */
   
}

.talent-dropdown input {
    margin-right: 5px;
}



</style>

    <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="輸入關鍵字..." class="search-input">
    <button type="submit" class="search-button">搜尋 <i class="bi bi-search"></i></button>
</form>


<!-- JavaScript -->
<script>
    const button = document.getElementById("toggleTalentDropdown");
    const dropdown = document.getElementById("talentDropdown");

    // 設定 dropdown 寬度與按鈕一致
    button.addEventListener("click", function () {
        // 確保寬度一致
        dropdown.style.width = button.offsetWidth + "px";
        
        // 顯示或隱藏下拉選單
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    // 更新按鈕文字
    document.querySelectorAll(".talent-option").forEach(function (input) {
        input.addEventListener("change", function () {
            const selected = Array.from(document.querySelectorAll(".talent-option:checked"))
                                  .map(input => input.value);
            button.textContent = selected.length > 0 ? selected.join(", ") : "選擇能力";
        });
    });

    // 點擊 dropdown 以外區域自動關閉
    document.addEventListener("click", function (event) {
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // 頁面載入後自動更新所有下拉選單
    updateAllSelectOptions();

    // 綁定選單變動事件
    $('#region').on('change', function () {
        updateSelectOptions('school_name');
        updateSelectOptions('department');
        updateSelectOptions('disc_cluster');
    });

    $('#school_name').on('change', function () {
        updateSelectOptions('disc_cluster');  // 只需更新學群
        updateSelectOptions('department');   // 也更新科系
    });

    $('#disc_cluster').on('change', function () {
        updateSelectOptions('department');   // 學群變動後更新科系
    });
});

// 取得目前選擇的篩選條件
function getCurrentFilters() {
    return {
        region: $('#region').val(),
        school_name: $('#school_name').val(),
        department: $('#department').val(),
        disc_cluster: $('#disc_cluster').val(),
    };
}

// 更新所有的下拉選單
function updateAllSelectOptions() {
    updateSelectOptions('school_name');
    updateSelectOptions('department');
    updateSelectOptions('disc_cluster');
}

// 更新特定的下拉選單
function updateSelectOptions(target) {
    const filters = getCurrentFilters();
    filters['target'] = target;

    const labels = {
        region: '地區',
        school_name: '學校',
        department: '科系',
        disc_cluster: '學群',
        plan: '計畫類別',
        ID: '身份',
        talent: '能力'
    };

    const urlParams = new URLSearchParams(window.location.search);
    const selectedValFromUrl = urlParams.get(target) || '';

    $.ajax({
        url: 'get_options.php',
        type: 'GET',
        data: filters,
        dataType: 'json',
        success: function(response) {
            const select = $('#' + target);
            const label = labels[target] || '項目';

            select.empty().append('<option value="">選擇' + label + '</option>');

            // 若沒有資料，則進行 fallback 重抓
            if (response.length === 0 && (target === 'disc_cluster' || target === 'department')) {
                const fallbackFilters = { target: target };

                if (target === 'disc_cluster') {
                    // 若學群無資料，重抓學群時保留學校條件
                    fallbackFilters.school_name = $('#school_name').val();
                }

                if (target === 'department') {
                    // 若科系無資料，重抓科系時保留學校與學群條件
                    fallbackFilters.school_name = $('#school_name').val();
                    fallbackFilters.disc_cluster = $('#disc_cluster').val(); // 保留學群條件
                }

                // 發送重抓請求
                $.ajax({
                    url: 'get_options.php',
                    type: 'GET',
                    data: fallbackFilters,
                    dataType: 'json',
                    success: function(fallbackResponse) {
                        // 這邊處理重抓後的資料
                        $.each(fallbackResponse, function(_, val) {
                            select.append('<option value="' + val + '">' + val + '</option>');
                        });
                    }
                });

                return; // 取消後續正常的資料處理，避免加重複選項
            }

            // 若有資料，正常 append 到選單
            $.each(response, function(_, val) {
                const selected = val === selectedValFromUrl ? ' selected' : '';
                select.append('<option value="' + val + '"' + selected + '>' + val + '</option>');
            });
        }
    });
}
</script>


</div>
<br>
      <div style="display: flex; justify-content: flex-end;">
        <?php if ($showAdminInterface): ?>
          <a href="add_school.php" class="btn btn-primary me-2" style="background-color: var(--accent-color); border: none;">
            <i class="bi bi-plus-circle"></i> 新增校系
          </a>
        <?php endif; ?>
        <form method="GET" >
        <?php if ($showAdminInterface): ?>
        <input type="hidden" name="admin" value="1">
        <?php endif; ?>
        <button type="submit" class="clear-button">重置 <i class="bi bi-arrow-clockwise"></i></button>
        </form>
      </div>

<?php if (!empty($results)): ?>
<table class="table table-striped table-hover align-middle text-center mt-4">
  <thead class="table-dark">
    <tr><th>比較</th><th>學校</th><th>科系</th><th>名額</th><th>詳細資料</th><th>收藏人數</th><th>收藏</th></tr>
  </thead>
  <tbody>
    
<?php foreach ($results as $row): ?>
<tr>

  <!-- ✅ 新增「比較」欄位 -->
  <td>
    <input type="checkbox" class="compare-checkbox" value="<?= htmlspecialchars($row['Sch_num']) ?>">
  </td>
  <td><?= htmlspecialchars($row['School_Name']); ?></td>
  
  <td><?= htmlspecialchars($row['Department']); ?></td>
  <td><?= htmlspecialchars($row['Quota']); ?></td>
  <td>
  <?php if ($showAdminInterface): ?>
    <a href="about-admin.php?sch_num=<?= urlencode($row['Sch_num']) ?>" class="btn btn-sm" style="background-color: var(--accent-color); color:white;">
      管理校系簡章
    </a>
  <?php else: ?>
    <a href="school_detail.php?sch_num=<?= urlencode($row['Sch_num']) ?>" class="btn btn-sm" style="background-color: var(--accent-color); color:white;">
      詳細介紹
    </a>
  <?php endif; ?>
  </td>
        


        <!-- 收藏人數與進度條 -->
        <td>
  <?php
    $connFav = new mysqli("localhost", "root", "", "SA-6");
    $totalUsersResult = $connFav->query("SELECT COUNT(DISTINCT User_ID) AS total_users FROM account");
    $totalUsers = $totalUsersResult->fetch_assoc()['total_users'] ?? 0;

    $stmt = $connFav->prepare("SELECT COUNT(DISTINCT User_ID) AS total_fav FROM my_favorites WHERE Sch_num = ?");
    $stmt->bind_param("s", $row['Sch_num']);
    $stmt->execute();
    $res = $stmt->get_result();
    $favCount = $res->fetch_assoc()['total_fav'] ?? 0;

    $percentage = ($totalUsers > 0) ? round($favCount / $totalUsers * 100) : 0;
    $schNum = htmlspecialchars($row['Sch_num']);
  ?>
  <div id="fav-info-<?= $schNum ?>">
    <?= $favCount ?> / <?= $totalUsers ?>
    <div class="progress mt-2" style="height: 10px;">
      <div class="progress-bar" id="fav-bar-<?= $schNum ?>" role="progressbar" 
        style="width: <?= $percentage ?>%; background-color: #FFCC00;" 
        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
      </div>
    </div>
  </div>
</td>

  



        <td>
    <!-- 收藏按鈕 -->
<button class="favorite-btn"   style="background-color: transparent; "
  data-sch-num="<?php echo $row['Sch_num']; ?>"
  onclick="toggleStar(this)">
  <i class="bi bi-star"></i>
</button>
    </td>
    

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
              // 🎯 加上這段即時更新收藏人數與進度條
      fetch('get_fav_count.php?sch_num=' + encodeURIComponent(schNum))
        .then(res => res.json())
        .then(data => {
          const infoDiv = document.getElementById('fav-info-' + schNum);
          const barDiv = document.getElementById('fav-bar-' + schNum);
          if (infoDiv && barDiv) {
            infoDiv.innerHTML = `
              ${data.fav} / ${data.total}
              <div class="progress mt-2" style="height: 10px;">
                <div class="progress-bar" id="fav-bar-${schNum}" role="progressbar" 
                  style="width: ${data.percent}%; background-color: #FFCC00;" 
                  aria-valuenow="${data.percent}" aria-valuemin="0" aria-valuemax="100">
                </div>
              </div>`;
          }
        });

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
  })
  .then(() => {
    // 🎯 這段會即時更新收藏人數和進度條
    fetch('get_fav_count.php?sch_num=' + encodeURIComponent(schNum))
      .then(res => res.json())
      .then(data => {
        const infoDiv = document.getElementById('fav-info-' + schNum);
        const barDiv = document.getElementById('fav-bar-' + schNum);
        if (infoDiv && barDiv) {
          infoDiv.innerHTML = `
            ${data.fav} / ${data.total}
            <div class="progress mt-2" style="height: 10px;">
              <div class="progress-bar" id="fav-bar-${schNum}" role="progressbar" 
                style="width: ${data.percent}%; background-color: #FFCC00;" 
                aria-valuenow="${data.percent}" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>`;
        }
      });
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
    color: #000;
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
    color: #000;
  }

  #comparison-result > div {
  background-color: #f9f9f9;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 12px;
  min-height: 300px;
  overflow-y: auto;
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


  <div id="comparison-section" style="display:none; margin-top:30px;">
  <h3 class="text-center mb-4">比較結果</h3>
  <div id="comparison-result" class="row">
    <div class="col-md-6" id="left-compare"></div>
    <div class="col-md-6" id="right-compare"></div>
  </div>
</div>

<script>
const compareCheckboxes = document.querySelectorAll('.compare-checkbox');

compareCheckboxes.forEach(checkbox => {
  checkbox.addEventListener('change', () => {
    const checked = document.querySelectorAll('.compare-checkbox:checked');

    if (checked.length > 2) {
      checkbox.checked = false;
      alert("最多只能選擇兩所學校進行比較");
      return;
    }

    if (checked.length === 2) {
      const sch1 = checked[0].value;
      const sch2 = checked[1].value;

      // ✅ 跳轉至新的比較頁面
      window.location.href = `compare_result.php?sch1=${encodeURIComponent(sch1)}&sch2=${encodeURIComponent(sch2)}`;
    }
  });
});


</script>


  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>