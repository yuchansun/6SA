<?php
include('header.php');
require_once("db.php");


// 查詢 my_favorites 資料表
$query = "SELECT * FROM my_favorites INNER JOIN sch_description ON my_favorites.Sch_num = sch_description.Sch_num";
$result = $conn->query($query);

$favorites = [];
while ($row = $result->fetch_assoc()) {
    // 儲存每一筆收藏的資料
    $favorites[] = [
        'Sch_num' => $row['Sch_num'],
        'School_Name' => $row['School_Name'],
        'Department' => $row['Department'],
        'Region' => $row['Region'],
        'Disc_Cluster' => $row['Disc_Cluster'],
        'Schol_Apti' => $row['Schol_Apti'],
        'Talent' => $row['Talent'],
        'ID' => $row['ID'],
        'Plan' => $row['Plan'],
        'Quota' => $row['Quota'],
        'Contact' => $row['Contact'],
        'link' => $row['link']
    ];
}

// 關閉資料庫連線
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>我的最愛</title>

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="portfolio-details-page">

  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1>我的最愛</h1>
        <p></p>
        
      </div>
    </div><!-- End Page Title -->

   
  </main>



    <!-- Portfolio Details Section -->
    <section id="portfolio-details" class="portfolio-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

      <div class="favorites-wrapper">
  <div id="favorite-list" class="d-flex flex-wrap gap-4"></div>
</div>


      </div>

    </section><!-- /Portfolio Details Section -->

  </main>
  <script>
  window.onload = function () {
    const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    const container = document.getElementById('favorite-list');

    if (favorites.length === 0) {
      container.innerHTML = "<p style='text-align:center;'>尚未收藏任何學校。</p>";
      return;
    }

    fetch('get_fav_detail.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ schNums: favorites })
    })
    .then(res => res.json())
    .then(data => {
      container.innerHTML = ''; // 清空原本內容

      data.forEach(school => {
        const div = document.createElement('div');
        div.className = 'portfolio-info';
        div.setAttribute('data-aos', 'fade-up');
        div.setAttribute('data-aos-delay', '200');

        const todoId = `todo-${school.Sch_num}`;

        div.innerHTML = `
  <div style="position: relative; padding-top: 30px;">
    <i class="bi bi-star-fill"
       onclick="toggleFavorite('${school.Sch_num}', this)"
       style="position: absolute; top: 0px; right: -5px; cursor: pointer; font-size: 22px;color: gold;"
       title="取消收藏"></i>

   <h3><a href="school_detail.php?sch_num=${school.Sch_num}" class="portfolio-title">
          ${school.School_Name} ${school.Department}
        </a></h3>
        <style>
          .portfolio-title {
            
            color: var(--heading-color);
          }
          .portfolio-title:hover {
           
          }
        </style>
        <ul>
          <li><strong>簡章網址</strong>: <a href="${school.link}" target="_blank">${school.link}</a></li>
        </ul>

    <div class="todo-section">
      <h5>To-Do List</h5>
      <ul id="${todoId}" class="todo-list"></ul>
    </div>
  </div>
`;

        container.appendChild(div);
        renderTodos(school.Sch_num);  // 取得並顯示該校系的 To-Do List
      });
    });
  };

// 取消收藏（適用於我的最愛頁面）
function toggleFavorite(schNum, iconElement) {
  let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

  const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
  const user_id = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

  if (favorites.includes(schNum)) {
    // 1. 移除 localStorage 中的收藏
    favorites = favorites.filter(fav => fav !== schNum);
    localStorage.setItem('favorites', JSON.stringify(favorites));

    // 2. 改變星星樣式
    iconElement.classList.remove('bi-star-fill');
    iconElement.classList.add('bi-star');
    iconElement.style.color = 'gray';

    // 3. 若有登入，再發送到後端刪除資料庫
    if (isLoggedIn) {
      fetch('remove_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'sch_num=' + encodeURIComponent(schNum),
        credentials: 'include'
      });
    }

    // 4. 更新畫面
    setTimeout(() => location.reload(), 300);
  }
}
function renderTodos(schNum, userId) {
  const list = document.getElementById(`todo-${schNum}`);
  if (!list) return;

  fetch('get_todolist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ schNum, userId })
  })
  .then(res => res.json())
  .then(todos => {
    list.innerHTML = '';

    if (!Array.isArray(todos) || todos.length === 0) {
      list.innerHTML = "<p>目前沒有待辦事項。</p>";
      return;
    }

    todos.forEach(todo => {
      const li = document.createElement('li');
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.checked = todo.is_done == 1;
      checkbox.addEventListener('change', () => {
        updateTodoStatus(userId, todo.todo_id, checkbox.checked);
      });

      li.appendChild(checkbox);
      li.innerHTML += `
        <strong>${todo.title}</strong><br>
        🕓 ${todo.start_time || ''} ～ ${todo.end_time || ''}
      `;
      list.appendChild(li);
    });
  })
  .catch(err => {
    console.error('載入待辦失敗:', err);
    list.innerHTML = "<p>載入失敗，請稍後再試。</p>";
  });
}

function updateTodoStatus(userId, todoId, isDone) {
  fetch('update_todo_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      userId,
      todoId,
      isDone: isDone ? 1 : 0
    })
  })
  .then(res => res.json())
  .then(data => {
    console.log("更新完成", data);
  })
  .catch(err => {
    console.error("更新失敗", err);
  });
}


    


</script>




    <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background-color: #fff;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #f5f5f5;
      color: #444;
    }

    button {
      background-color: #ff6666;
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #cc0000;
    }
    
    #favorite-list {
  display: flex;

  gap: 20px;
 
}

.portfolio-info {
  width: 300px;
  min-height: 250px;
  padding: 20px;
  transition: 0.3s;
}

  </style>
  
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
