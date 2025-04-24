<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST['account'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'sa-6');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($password === $user['Password']) {
            $_SESSION['user'] = $user['E-mail'];
            $_SESSION['nickname'] = $user['Nickname'];

            // 修改跳轉邏輯
            $redirectTo = $_SESSION['redirect_to'] ?? 'index.php'; // 沒有就回首頁
            unset($_SESSION['redirect_to']); // 清除 redirect 資訊
            header("Location: $redirectTo");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Incorrect account.";
    }

    $stmt->close();
    $conn->close();
}
?>
