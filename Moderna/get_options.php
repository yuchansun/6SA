<?php
include 'db.php';

$target = $_GET['target'] ?? 'School_Name';

// 篩選條件
$filters = [
    'Region' => $_GET['region'] ?? '',
    'School_Name' => $_GET['school_name'] ?? '',
    'Disc_Cluster' => $_GET['disc_cluster'] ?? '',
    'Department' => $_GET['department'] ?? '',
];

$where = [];
$params = [];
$types = '';

// 根據每個條件類型動態設定 `types`
foreach ($filters as $key => $val) {
    if ($val !== '') {
        $where[] = "$key = ?";
        $params[] = $val;
        $types .= 's';  // 這裡假設所有條件都是字串，如果有其他類型需要調整
    }
}

// 構建 SQL 查詢
$sql = "SELECT DISTINCT `$target` FROM sch_description";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY `$target` ASC";

// 執行查詢
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    // 綁定參數
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row[$target];
}
echo json_encode($options);
?>
