<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && isset($_POST['sch_num'])) {
    $userId = $_SESSION['user_id'];
    $schNum = $_POST['sch_num'];

    $stmt = $conn->prepare("DELETE FROM my_favorites WHERE user_id = ? AND sch_num = ?");
    $stmt->bind_param("ss", $userId, $schNum);
    $stmt->execute();
    $stmt->close();
}
?>
