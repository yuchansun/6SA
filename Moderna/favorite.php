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

    fetch('get_favorites.php', {
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
          <h3>${school.School_Name} ${school.Department}</h3>
          <ul>
            <li><strong>學校名稱</strong>: ${school.School_Name}</li>
            <li><strong>科系</strong>: ${school.Department}</li>
            <li><strong>地區</strong>: ${school.Region}</li>
            <li><button onclick="removeFavorite('${school.Sch_num}')">取消收藏</button></li>
          </ul>

          <div class="todo-section">
            <h5>To-Do List</h5>
            <ul id="${todoId}" class="todo-list"></ul>
          </div>
        `;

        container.appendChild(div);
        renderTodos(school.Sch_num);  // 取得並顯示該校系的 To-Do List
      });
    });
  };

  // 取消收藏
  function removeFavorite(schNum) {
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    favorites = favorites.filter(fav => fav !== schNum);
    localStorage.setItem('favorites', JSON.stringify(favorites));
    location.reload();
  }

  // 渲染 To-Do List
  function renderTodos(schNum) {
    const list = document.getElementById(`todo-${schNum}`);
    if (!list) {
      console.error(`找不到 todo list 元素: todo-${schNum}`);
      return;
    }

    fetch('get_todolist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ schNum: schNum })
    })
    .then(res => res.json())
    .then(todos => {
      list.innerHTML = '';  // 清空原本內容

      if (!todos || todos.length === 0) {
        list.innerHTML = "<p>目前沒有待辦事項。</p>";
        return;
      }

      // 完成的排最上面
      const sorted = [...todos].sort((a, b) => b.completed - a.completed);

      sorted.forEach((todo, index) => {
        const li = document.createElement('li');
        li.innerHTML = `
          <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" ${todo.completed ? 'checked' : ''} onchange="toggleComplete('${schNum}', ${index})">
            <span style="text-decoration: ${todo.completed ? 'line-through' : 'none'}">${todo.text}</span>
          </label>
        `;
        list.appendChild(li);
      });
    });
  }

  // 標記為完成或未完成
  function toggleComplete(schNum, index) {
    fetch('toggle_todolist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ schNum: schNum, index: index })
    })
    .then(res => res.json())
    .then(response => {
      renderTodos(schNum);  // 更新 To-Do List
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
