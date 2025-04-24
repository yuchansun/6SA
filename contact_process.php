// 在登入成功後，檢查是否有 redirect_to 的 session
if (isset($_SESSION['redirect_to'])) {
    $redirectTo = $_SESSION['redirect_to'];
    unset($_SESSION['redirect_to']); // 清除 session 中的 redirect_to
    header("Location: $redirectTo"); // 跳轉到目標頁面
    exit();
} else {
    header("Location: index.php"); // 預設跳轉到首頁
    exit();
}