<?php
if (!isset($_GET['Sch_num'])) {
  die("Invalid request.");
}

$sch_num = $_GET['Sch_num'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'sa-group6');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch detailed data
$sql = "SELECT * FROM sch_description WHERE Sch_num = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
} else {
  die("No data found.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>詳細介紹</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-header bg-primary text-white text-center">
        <h3>詳細介紹</h3>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <tr>
            <th>學校</th>
            <td><?php echo $row['School_Name']; ?></td>
          </tr>
          <tr>
            <th>科系</th>
            <td><?php echo $row['Department']; ?></td>
          </tr>
          <tr>
            <th>地區</th>
            <td><?php echo $row['Region']; ?></td>
          </tr>
          <tr>
            <th>學類</th>
            <td><?php echo $row['Disc_Cluster']; ?></td>
          </tr>
          <tr>
            <th>興趣</th>
            <td><?php echo $row['Schol_Apti']; ?></td>
          </tr>
          <tr>
            <th>能力</th>
            <td><?php echo $row['Talent']; ?></td>
          </tr>
          <tr>
            <th>招收身分</th>
            <td><?php echo $row['ID']; ?></td>
          </tr>
          <tr>
            <th>計畫類別</th>
            <td><?php echo $row['Plan']; ?></td>
          </tr>
          <tr>
            <th>名額</th>
            <td><?php echo $row['Quota']; ?></td>
          </tr>
          <tr>
            <th>電話</th>
            <td><?php echo $row['Contact']; ?></td>
          </tr>
          <tr>
            <th>連結</th>
            <td><a href="<?php echo $row['link']; ?>" target="_blank"><?php echo $row['link']; ?></a></td>
          </tr>
        </table>
      </div>
      <div class="card-footer text-center">
        <a href="about.php" class="btn btn-secondary">返回</a>
      </div>
    </div>
  </div>
</body>
</html>
