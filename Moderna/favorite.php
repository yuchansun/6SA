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
        'Talent' => $row['Talent'],
      
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
        <p>點擊標題可連結到該校系的詳細介紹頁面。登入後可查看每個收藏的待辦事項，並勾選完成狀態及是否開啟通知功能。點擊右上角的金色星星可取消收藏。</p>
        
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

  const userId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

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

 <h3>
  <a href="school_detail.php?sch_num=${school.Sch_num}" class="portfolio-title">
    ${school.School_Name} ${school.Department}
  </a>
</h3>

<style>
 
  .portfolio-title {
   
    color: var(--heading-color); /* 這裡指定顏色 */
  }
</style>


          <ul>
           <li><strong>簡章網址</strong>: <a href="${school.link}" target="_blank" class="wrap-link">${school.link}</a></li>
<style>
.wrap-link {
  display: -webkit-box;
  -webkit-line-clamp: 1; /* 限制顯示2行 */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-all;
}
</style>

          </ul>

          <div class="todo-section">
            <h5>To-Do List</h5>
            <ul id="${todoId}" class="todo-list"></ul>
          </div>
        </div>
      `;

      container.appendChild(div);
      renderTodos(school.Sch_num, userId); // ✅ 修正這裡，加上 userId
    });
  });
};


// 取消收藏（適用於我的最愛頁面）
function toggleFavorite(schNum, iconElement) {
  let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

  const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
  const user_id = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

  if (favorites.includes(schNum)) {
    favorites = favorites.filter(fav => fav !== schNum);
    localStorage.setItem('favorites', JSON.stringify(favorites));

    iconElement.classList.remove('bi-star-fill');
    iconElement.classList.add('bi-star');
    iconElement.style.color = 'gray';

    if (isLoggedIn) {
      fetch('remove_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'sch_num=' + encodeURIComponent(schNum),
        credentials: 'include'
      });
    }

    location.reload();
  }
}

function renderTodos(schNum, userId) {
  const list = document.getElementById(`todo-${schNum}`);
  if (!list) return;

  if (!schNum || !userId) {
  console.error("未登入，無法載入待辦清單！");
  list.innerHTML = `
    <a href="contact.php?redirect=favorite.php">
      請先登入以查看待辦清單。
    </a>`;
  return;
}


  console.log("載入待辦清單，schNum:", schNum, "userId:", userId);

  fetch('get_todolist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ schNum, userId })
  })
    .then(res => res.json())
    .then(todos => {
      console.log("收到待辦清單資料:", todos);
      list.innerHTML = '';

      if (!Array.isArray(todos) || todos.length === 0) {
        list.innerHTML = "<p>目前沒有待辦事項。</p>";
        return;
      }

      const now = new Date();
      let hasShownAlert = false;

      todos.forEach(todo => {
        const li = document.createElement('li');
        li.style.marginBottom = '10px';

        const todoWrapper = document.createElement('div');
        todoWrapper.style.display = 'flex';
        todoWrapper.style.alignItems = 'center';
        todoWrapper.style.justifyContent = 'space-between';

        const leftContent = document.createElement('div');
        leftContent.style.display = 'flex';
        leftContent.style.alignItems = 'center';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = todo.is_done === 1;
        checkbox.style.marginRight = '8px';

        checkbox.addEventListener('change', () => {
          updateTodoStatus(userId, todo.todo_id, checkbox.checked ? 1 : 0);
        });

        const title = document.createElement('strong');
        title.textContent = todo.title;

        // 判斷是否已過期或快到期
        if (todo.end_time) {
          const endTime = new Date(todo.end_time.replace(/-/g, '/'));
          const hoursLeft = (endTime - now) / (1000 * 60 * 60);

          if (endTime < now && todo.is_done !== 1) {
            title.style.textDecoration = 'line-through';
            title.style.color = 'gray';
            title.textContent = todo.title + '（已過期）';
          } else if (hoursLeft <= 24 && todo.is_done !== 1) {
            title.style.color = 'red';
            title.textContent = '⏰ ' + todo.title;

            if (!hasShownAlert) {
              alert(`提醒：有待辦事項即將到期！ (${todo.title})`);
              hasShownAlert = true;
            }
          }
        }

        leftContent.appendChild(checkbox);
        leftContent.appendChild(title);

        // 右邊的圖示，行事曆圖示和鈴鐺圖示
        const rightContent = document.createElement('div');
        rightContent.style.display = 'flex';
        rightContent.style.alignItems = 'center';

        const calendarIcon = document.createElement('i');
        calendarIcon.className = 'bi bi-calendar';
        calendarIcon.style.cursor = 'pointer';
        calendarIcon.style.marginLeft = '10px';

        const timeInfo = document.createElement('div');
        timeInfo.className = 'time-tag';
        timeInfo.style.display = 'none';
        timeInfo.style.marginTop = '5px';

        if (todo.start_time || todo.end_time) {
          if (todo.start_time && todo.end_time) {
            timeInfo.textContent = `${todo.start_time} ～ ${todo.end_time}`;
          } else if (todo.start_time) {
            timeInfo.textContent = `${todo.start_time}`;
          } else {
            timeInfo.textContent = `${todo.end_time}`;
          }

          const now = new Date();
          const deadline = new Date(todo.end_time || todo.start_time);
          const timeDiff = deadline - now;
          const isNearOrExpired = timeDiff <= 24 * 60 * 60 * 1000;

          if (isNearOrExpired) {
            timeInfo.style.backgroundColor = '#ffe5e5';  // 紅底
            timeInfo.style.padding = '4px 8px';
            timeInfo.style.borderRadius = '6px';
            timeInfo.style.color = 'black';

            calendarIcon.style.color = 'red';  // 圖示紅色
          }
        }

        calendarIcon.title = '點擊查看時間';
        calendarIcon.addEventListener('click', () => {
          const isHidden = timeInfo.style.display === 'none';
          timeInfo.style.display = isHidden ? 'block' : 'none';
          calendarIcon.className = isHidden ? 'bi bi-calendar-x' : 'bi bi-calendar';
          calendarIcon.title = isHidden ? '點擊隱藏時間' : '點擊查看時間';
        });

        const bellIcon = document.createElement('i');
bellIcon.className = 'bi bi-bell-slash';  // 預設為劃線鈴鐺（表示關閉通知）
bellIcon.style.cursor = 'pointer';
bellIcon.style.marginLeft = '10px';

// 初始化鈴鐺顏色與樣式
bellIcon.style.transition = 'color 0.3s ease';  // 添加過渡動畫
if (todo.is_notified == 1) {
  bellIcon.style.color = 'gold';  // 如果已通知，顯示金色鈴鐺
  bellIcon.className = 'bi bi-bell';  // 已通知，填滿鈴鐺
  bellIcon.title = '關閉通知';  // 顯示「關閉通知」的提示文字
} else {
  bellIcon.style.color = 'gray';  // 如果未通知，顯示灰色鈴鐺
  bellIcon.className = 'bi bi-bell-slash';  // 未通知，顯示劃線鈴鐺
  bellIcon.title = '開啟通知';  // 顯示「開啟通知」的提示文字
}

// 點擊鈴鐺圖示後更新通知狀態
bellIcon.addEventListener('click', () => {
  const newStatus = bellIcon.style.color === 'gray';  // 如果鈴鐺是灰色，則視為未通知，點擊後應該變為已通知

  // 更新鈴鐺顏色與樣式
  bellIcon.style.color = newStatus ? 'gold' : 'gray';  // 更新顏色
  bellIcon.className = newStatus ? 'bi bi-bell' : 'bi bi-bell-slash';  // 切換為填滿或劃線鈴鐺

  // 更新提示文字
  bellIcon.title = newStatus ? '關閉通知' : '開啟通知';  // 根據狀態改變 tooltip 提示文字

  // 更新資料庫
  updateNotificationStatus(userId, todo.todo_id, newStatus ? 1 : 0);  // 傳遞新狀態到後端
});

        rightContent.appendChild(calendarIcon);
        rightContent.appendChild(bellIcon);

        todoWrapper.appendChild(leftContent);
        todoWrapper.appendChild(rightContent);

        li.appendChild(todoWrapper);
        li.appendChild(timeInfo);
        list.appendChild(li);
      });
    })
    .catch(err => {
      console.error("載入待辦清單失敗:", err);
      list.innerHTML = "<p>載入待辦清單失敗，請稍後再試。</p>";
    });
}
function updateTodoStatus(userId, todoId, isDone) {
  fetch('update_todo_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userId, todoId, isDone })
  })
  .then(response => response.text())  
  .then(text => {
    console.log('伺服器回傳:', text);
    try {
      const data = JSON.parse(text);
      console.log('轉換後資料:', data);
    } catch (err) {
      console.error('解析失敗:', err);
      console.log('伺服器回應非 JSON 格式，請檢查 PHP 檔案回傳內容');
    }
  })
  .catch(error => {
    console.error('更新失敗:', error);
  });
}

function updateNotificationStatus(userId, todoId, isNotified) {
  fetch('update_notification_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      userId: userId,
      todoId: todoId,
      isNotified: isNotified
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log(data); // 可以看到通知狀態是否更新
  })
  .catch(error => {
    console.error('更新通知狀態時出錯:', error);
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
 .time-tag {
            background-color:  color-mix(in srgb, var(--default-color), transparent 94%);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
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
