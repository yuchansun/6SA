<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  echo json_encode([]);
  exit();
}

$conn = new mysqli("localhost", "root", "", "sa-6");
$user_id = $_SESSION['user_id'];
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

