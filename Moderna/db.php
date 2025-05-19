<?php
// 資料庫連接設定
$servername = "sql310.infinityfree.com"; // 您的主機名稱
$username = "if0_39019350"; // 您的使用者名
$password = "kblzKONpyHZlKk"; // 您的密碼
$dbname = "if0_39019350_6sa"; // 您的資料庫名稱
$port = 3306; // 連接埠

// 建立與資料庫的連線
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}

// 設定字符集
$conn->set_charset("utf8mb4");

$result = $conn->query("SELECT * FROM latest_news LIMIT 1");
$row = $result->fetch_assoc();
var_dump($row);

$conn->close();
?>
