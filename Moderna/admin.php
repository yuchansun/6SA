<?php
session_start();
require_once 'db.php';

$host = "localhost";
$db = "sa-6";
$user = "root";
$pass = "";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure user is logged in by checking session
if (!isset($_SESSION['user_id'])) {
    echo "⛔ Access Denied. You are not logged in.";
    exit();
}

// Fetch the role directly from the database
$user_id = $_SESSION['user_id']; // assuming user_id is stored in the session
$sql = "SELECT Roles, Nickname FROM account WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    // Check if the role is '管理者'
    if ($row['Roles'] !== '管理者') {
        echo "⛔ Access Denied. You are not an administrator.";
        exit();
    }

    // If role is '管理者', continue with the page logic
    $nickname = $row['Nickname'];
} else {
    echo "❌ User not found.";
    exit();
}
?>
<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel</title>

    <!-- Favicons -->
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto&family=Poppins&family=Raleway&display=swap" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/vendor/aos/aos.css" rel="stylesheet">
<link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

<!-- Main CSS File -->
<link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <style>
       body  #header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
            background: rgba(0, 55, 67, 0.95);
        }

        .container {
            margin-top: 100px; /* Adjust for fixed header */
        }

        .card-header {
            background-color: #0073e6;
            color: white;
            font-weight: bold;
        }

        .card-body {
            background-color: #f8f9fa;
        }

        .card-body a {
            text-decoration: none;
        }

        .card-body a:hover {
            background-color: #0056b3;
            color: white;
        }

        .btn {
            font-size: 16px;
        }

        h2 {
            font-family: 'Roboto', sans-serif;
            font-size: 32px;
            color: #003747;
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .card mb-4 {
            margin-bottom: 20px;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .header-content {
            
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .header-content h1 {
            font-size: 36px;
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <!-- Main content -->
    <div class="container">
        <div class="header-content">
            <h1>👨‍💼 Welcome, <?= htmlspecialchars($nickname) ?> (管理者)</h1>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                🗨️ 管理討論區貼文
            </div>
            <div class="card-body">
                <p>查看、編輯或刪除使用者發佈的貼文。您擁有完全的權限來管理討論區。.</p>
                <a href="manage_posts.php" class="btn btn-primary btn-block">前往貼文管理</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                ✅ 驗證教師身份
            </div>
            <div class="card-body">
                <p>批准或拒絕教師身份驗證請求。確保所有教師資料已經驗證。.</p>
                <a href="teacher_verify.php" class="btn btn-success btn-block">前往驗證頁面</a>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
