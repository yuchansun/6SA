<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "sa-6";

// 建立資料庫連線
$conn = new mysqli($host, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
?>
