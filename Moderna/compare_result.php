<?php
include 'db.php';

$sch1 = $_GET['sch1'] ?? '';
$sch2 = $_GET['sch2'] ?? '';

function getSchoolData($conn, $sch_num) {
    $stmt = $conn->prepare("SELECT * FROM sch_description WHERE Sch_num = ?");
    $stmt->bind_param("s", $sch_num);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$data1 = getSchoolData($conn, $sch1);
$data2 = getSchoolData($conn, $sch2);
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>學校比較結果</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .compare-box {
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      background-color: #f8f8f8;
    }

    .compare-box p {
      margin-bottom: 10px;
    }

    .compare-box h4 {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <h2 class="text-center mb-4">比較結果</h2>
  <div class="row">
    <div class="col-md-6 compare-box">
      <?php if ($data1): ?>
        <h4><?= htmlspecialchars($data1['School_Name']) ?> - <?= htmlspecialchars($data1['Department']) ?></h4>
        <p><strong>地區：</strong><?= htmlspecialchars($data1['Region']) ?></p>
        <p><strong>學群：</strong><?= htmlspecialchars($data1['Disc_Cluster']) ?></p>
        <p><strong>招生名額：</strong><?= htmlspecialchars($data1['Quota']) ?></p>
       
        <p><strong>簡章連結：</strong><a href="<?= htmlspecialchars($data1['link']) ?>" target="_blank">點我查看</a></p>
        <p><strong>招生說明：</strong><?= nl2br(htmlspecialchars($data1['requirement'])) ?></p> <!-- 新增招生說明 -->
        
      <?php else: ?>
        <p>找不到第一筆資料。</p>
      <?php endif; ?>
    </div>
    <div class="col-md-6 compare-box">
      <?php if ($data2): ?>
        <h4><?= htmlspecialchars($data2['School_Name']) ?> - <?= htmlspecialchars($data2['Department']) ?></h4>
        <p><strong>地區：</strong><?= htmlspecialchars($data2['Region']) ?></p>
        <p><strong>學群：</strong><?= htmlspecialchars($data2['Disc_Cluster']) ?></p>
        <p><strong>招生名額：</strong><?= htmlspecialchars($data2['Quota']) ?></p>
        <p><strong>簡章連結：</strong><a href="<?= htmlspecialchars($data2['link']) ?>" target="_blank">點我查看</a></p>
        <p><strong>招生說明：</strong><?= nl2br(htmlspecialchars($data2['requirement'])) ?></p> <!-- 新增招生說明 -->
        <p>找不到第二筆資料。</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
