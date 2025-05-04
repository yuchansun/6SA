<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['teacher_id'];

    $conn = new mysqli('localhost', 'root', '', 'sa-6');
    if ($conn->connect_error) {
        die("資料庫連線失敗：" . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE teacher_info SET verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: teacher_verify.php");  // 在這裡進行重定向
    exit();  // 確保重定向後不再執行後續代碼
}
?>
