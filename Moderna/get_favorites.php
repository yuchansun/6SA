<?php
session_start();

// 檢查使用者是否登入
if (!isset($_SESSION['user_id'])) {
  echo json_encode([]);
  exit();
}

// 引入資料庫連線
require_once("db.php");

$user_id = $_SESSION['user_id'];

// 查詢使用者的收藏學校編號
$sql = "SELECT sch_num FROM my_favorites WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
  $favorites[] = $row['sch_num'];
}

echo json_encode($favorites);
?>
