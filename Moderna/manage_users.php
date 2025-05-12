<?php
session_start();
include('header.php'); 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("無權訪問：session 中找不到 user_id");
}

$user_id = $_SESSION['user_id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "sa-6");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user role from DB
$sql = "SELECT Roles FROM account WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    if ($row['Roles'] !== '管理者') {
        die("無權訪問：您不是管理者");
    }
} else {
    die("無法獲取角色資料，請檢查資料庫");
}

// If role is admin, fetch user list
$result = $conn->query("SELECT User_ID, Nickname, Roles, `E-mail` FROM account ORDER BY User_ID ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>管理者新增</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

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
    <style>
        #header {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 999;
  background: rgba(0, 55, 67, 0.95);
}
        body {
            font-family: Arial;
            background: #f2f2f2;
        }
        .container {
            margin-top: 100px;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #004d4d;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        button {
            background-color: #008080;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #006666;
        }
        .disabled-btn {
            background-color: #aaa;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<?php if ($row['Roles'] === '管理者'): ?>
    <div class="container">
        <h2>管理使用者角色</h2>
        <table>
            <tr>
                <th>User ID</th>
                <th>昵稱</th>
                <th>Role</th>
                <th>Email</th>
                <th>動作</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row["User_ID"] ?></td>
                <td><?= htmlspecialchars($row["Nickname"]) ?></td>
                <td><?= $row["Roles"] ?></td>
                <td><?= htmlspecialchars($row["E-mail"]) ?></td>
                <td>
                    <?php if ($row["Roles"] !== "管理者"): ?>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?= $row["User_ID"] ?>">
                        <button type="submit" name="make_admin">變改管理者</button>
                    </form>
                    <?php else: ?>
                        <button class="disabled-btn" disabled>已管理者</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php else: ?>
    <div style="text-align:center; margin-top:100px;">
        <h2 style="color:red;">此頁僅限管理者觀看。</h2>
    </div>
<?php endif; ?>

<?php $conn->close(); ?>
<?php include('footer.php'); ?>
</body>
</html>
