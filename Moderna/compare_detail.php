<?php
// compare_detail.php
include 'db.php';

$sch_num = $_GET['sch_num'] ?? '';
$sql = "SELECT * FROM sch_description WHERE Sch_num = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$conn->close();

if ($data):
?>
  <div>
    <h4><?= htmlspecialchars($data['School_Name']) ?> - <?= htmlspecialchars($data['Department']) ?></h4>
    <p><strong>地區：</strong><?= htmlspecialchars($data['Region']) ?></p>
    <p><strong>學群：</strong><?= htmlspecialchars($data['Disc_Cluster']) ?></p>
    <p><strong>招生名額：</strong><?= htmlspecialchars($data['Quota']) ?></p>
    <p><a href="<?= htmlspecialchars($data['link']) ?>" target="_blank">點我前往簡章</a></p>
  </div>
<?php
else:
  echo "<p>找不到資料。</p>";
endif;
?>
