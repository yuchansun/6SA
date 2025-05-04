<?php
session_start();

// 確認 session 中有 user_id
if (!isset($_SESSION['user_id'])) {
    die("無權訪問：session 中找不到 user_id");
}

$user_id = $_SESSION['user_id'];  // 確保 session 中有 user_id

// 連接資料庫
$conn = new mysqli('localhost', 'root', '', 'sa-6');
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];

    // 更新教師資料為已拒絕
    $stmt = $conn->prepare("UPDATE teacher_info SET verified = -1 WHERE id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();

    // 跳轉回待審核教師清單頁面
    header("Location: teacher_verify.php");
    exit();
}
