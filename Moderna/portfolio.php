<?php include('header.php'); ?>
<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
<script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Portfolio - Moderna Bootstrap Template</title>
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

  <!-- =======================================================
  * Template Name: Moderna
  * Template URL: https://bootstrapmade.com/free-bootstrap-template-corporate-moderna/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
  <style>
   
 
.portfolio-flters {
  display: flex;
  justify-content: center;  /* Centers the items horizontally */
  gap: 15px;  /* Adds space between the filter buttons */
  margin: 20px 0;  /* Optional: Adds top and bottom margin for spacing */
}
  
.portfolio-flters li {
  padding: 10px 20px; /* Adjust the padding for button size */
  font-size: 26px; /* Adjust the font size */
  margin-right: 10px; /* Space between buttons */
  border-radius: 5px; /* Optional: rounded corners */
  cursor: pointer;
  transition: all 0.3s ease;
  list-style-type: none;
  
}

.portfolio-flters li:hover {
  background-color: #f8f9fa; /* Change background color on hover */
}

.portfolio-flters li.filter-active {
  background-color:rgba(3, 61, 93, 0.7); /* Highlight active button */
  color: #fff;
}
.portfolio-content {
  position: relative;
  overflow: hidden;
  height: 250px; /* Adjust as needed */
}

.portfolio-content img {
  width: 100%;
  height: 350px;
  margin-bottom: 40px;
  transition: transform 0.3s ease-in-out;
}

.portfolio-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 10px 0;
  background-color: rgba(0, 0, 0, 0.5);
  color: white;
  display: flex;
  justify-content: center;
  gap: 10px;
  font-size: 14px;
  opacity: 0; /* Initially hide the buttons */
  transform: translateY(20px); /* Move the buttons out of view initially */
  transition: opacity 0.3s ease, transform 0.3s ease; /* Transition for visibility and position */
}

.portfolio-info a {
  color: white;
  text-decoration: none;
  padding: 5px 10px;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 5px;
  transition: transform 0.3s ease, opacity 0.3s ease, font-size 0.3s ease; /* Transition for scaling effect */
}
.portfolio-info a:hover {
  background-color: rgba(0, 0, 0, 0.9);
  transform: scale(1.2); /* Scale the buttons when hovered */
  font-size: 16px; /* Increase font size on hover */
}

.portfolio-content:hover .portfolio-info {
  opacity: 1; /* Show the buttons when hovering over the image */
  transform: translateY(0); /* Bring the buttons into view */
}

.portfolio-content:hover img {
  transform: scale(1.1); /* Optionally, scale the image slightly on hover */
}

  .portfolio-container {
    margin: 10px;
    
  }
</style>
</head>

<body class="portfolio-page">

 

  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1>學校簡介</h1>
        <p>介紹台灣國立，私立的大學</p>
        
      </div>
    </div><!-- End Page Title -->
    
<div class="portfolio-isotope" data-portfolio-filter="*" data-portfolio-layout="masonry" data-portfolio-sort="original-order">

<!-- Filter Buttons -->
<ul class="portfolio-flters" data-aos="fade-up" data-aos-delay="100">
  <li data-filter="*" class="filter-active">全部</li>
  <li data-filter=".filter-product">國立</li>
  <li data-filter=".filter-app">私立</li>
</ul>

<!-- Portfolio Container -->
<div class="row gy-4 portfolio-container" data-aos="fade-up" data-aos-delay="200">
    <?php
include('db.php'); // if you have a DB connection file

$sql = "SELECT SchoolName, Sch_Intro, image_url, website, school_type FROM school_introduction";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $school_name = htmlspecialchars($row['SchoolName']);
    $description = htmlspecialchars($row['Sch_Intro']);
    $image_url = htmlspecialchars($row['image_url']);
    $website = htmlspecialchars($row['website']);
    $school_type = htmlspecialchars($row['school_type']); // 私立 or 國立

    // Determine filter class
    $filter_class = $school_type === '私立' ? 'filter-app' : 'filter-product';
?>
    <div class="col-lg-4 col-md-6 portfolio-item isotope-item <?php echo $filter_class; ?>">
  <h4><?php echo $school_name; ?></h4>
  <div class="portfolio-content h-100 position-relative">
    <img src="<?php echo $image_url; ?>" class="img-fluid" alt="">
    <div class="portfolio-info position-absolute bottom-0 w-100 d-flex justify-content-center align-items-center">
      <a href="<?php echo $website; ?>" target="_blank" title="More Details" class="details-link mx-2"><i class="bi bi-link-45deg"></i> 網站</a>
      <a href="<?php echo $image_url; ?>" title="<?php echo $description; ?>" data-gallery="portfolio-gallery" class="glightbox preview-link mx-2"><i class="bi bi-zoom-in"></i> 介紹</a>
    </div>
  </div>
</div>
<?php
  }
} else {
  echo "<p>No schools found.</p>";
}
?>


              </div><!-- End Portfolio Container -->

          </div>

      </div>

  </section><!-- /Portfolio Section -->

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
  <script>
  // Initialize Isotope
  var portfolioIsotope = new Isotope('.portfolio-container', {
    itemSelector: '.portfolio-item',
    layoutMode: 'masonry',
    filter: '*' // Default filter (All)
  });

  // Filter items when filter button is clicked
  var portfolioFilters = document.querySelectorAll('.portfolio-flters li');
  portfolioFilters.forEach(function(filterBtn) {
    filterBtn.addEventListener('click', function() {
      var filterValue = this.getAttribute('data-filter');
      portfolioIsotope.arrange({ filter: filterValue });

      // Add/remove active class
      portfolioFilters.forEach(function(btn) {
        btn.classList.remove('filter-active');
      });
      this.classList.add('filter-active');
    });
  });
</script>

</body>

</html>