<?php
session_start();

header('Content-Type: application/json');

// 檢查是否登入
if (!isset($_SESSION['user_id'])) {
  echo json_encode([]);
  exit();
}

require_once("db.php");

$user_id = $_SESSION['user_id'];

// 查詢收藏
$sql = "SELECT sch_num FROM my_favorites WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  echo json_encode(["error" => "SQL prepare failed"]);
  exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
  $favorites[] = $row['sch_num'];
}

echo json_encode($favorites);
?>
