<?php
session_start();

// 確保有接收到 POST 參數
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sch_num = $_POST['sch_num'];
    $user_id = $_POST['user_id'];

    // 檢查必填欄位
    if (empty($sch_num) || empty($user_id)) {
        echo "缺少必要的參數";
        exit();
    }

    // 連接資料庫
    $conn = new mysqli("localhost", "root", "", "sa-6");
    if ($conn->connect_error) {
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    // 防止重複加入收藏
    $check_sql = "SELECT * FROM my_favorites WHERE user_id = ? AND sch_num = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $user_id, $sch_num); // 應該是 "is"，`user_id` 是整數，`sch_num` 是字串
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // 沒有重複則插入
        $insert_sql = "INSERT INTO my_favorites (user_id, sch_num) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("is", $user_id, $sch_num); // 確保綁定的類型是正確的
        if ($insert_stmt->execute()) {
            echo "已加入收藏";
        } else {
            echo "錯誤: " . $conn->error;
        }
        $insert_stmt->close();
    } else {
        echo "該資料已經存在";
    }

    $stmt->close();
    $conn->close();
}
?>
<script>
    .then(data => {
  console.log("後端回應：", data); // 請貼這行出來看看
});
</script>