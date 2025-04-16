<?php
require_once("db.php");

// 取得前端傳來的 JSON 並解碼
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['schNums']) || !is_array($data['schNums'])) {
    echo json_encode([]);
    exit;
}

$schNums = $data['schNums'];

// 將 schNums 裡的每一個都轉成安全字串
$escaped = array_map(function($num) use ($conn) {
    return "'" . $conn->real_escape_string($num) . "'";
}, $schNums);

$inClause = implode(",", $escaped);

// 查詢符合的學校資料
$query = "SELECT * FROM sch_description WHERE Sch_num IN ($inClause)";
$result = $conn->query($query);

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

$conn->close();
echo json_encode($favorites);
?>
 