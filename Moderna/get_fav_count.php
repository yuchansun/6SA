<?php
if (!isset($_GET['sch_num'])) {
    http_response_code(400);
    echo json_encode(['error' => '缺少參數']);
    exit;
}

$sch_num = $_GET['sch_num'];
$conn = new mysqli("localhost", "root", "", "SA-6");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫連線失敗']);
    exit;
}

// 取得總使用者數
$result = $conn->query("SELECT COUNT(DISTINCT User_ID) AS total_users FROM account");
$totalUsers = $result->fetch_assoc()['total_users'] ?? 0;

// 取得該 Sch_num 的收藏人數
$stmt = $conn->prepare("SELECT COUNT(DISTINCT User_ID) AS total_fav FROM my_favorites WHERE Sch_num = ?");
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$res = $stmt->get_result();
$favCount = $res->fetch_assoc()['total_fav'] ?? 0;

$conn->close();

// 回傳 JSON
header('Content-Type: application/json');
echo json_encode([
    'fav' => $favCount,
    'total' => $totalUsers,
    'percent' => ($totalUsers > 0) ? round($favCount / $totalUsers * 100) : 0
]);
