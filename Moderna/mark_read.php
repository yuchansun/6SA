
<?php
require_once 'db.php'; // 資料庫連接

if (isset($_GET['id'])) {
    $notificationId = $_GET['id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '更新失敗']);
    }
    $stmt->close();
}
?>
