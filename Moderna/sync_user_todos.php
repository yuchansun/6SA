<?php
function sync_user_todos($mysqli, $user_id, $sch_num) {
    // 1. 新增：找出 todos 中該校系的 todo，但 user_todos 尚未加入的
    $insert_sql = "
        INSERT INTO user_todos (user_id, todo_id, is_done, updated_at, is_notified)
        SELECT ?, t.todo_id, 0, NOW(), 0
        FROM todos t
        WHERE t.Sch_num = ?
        AND NOT EXISTS (
            SELECT 1 FROM user_todos ut
            WHERE ut.user_id = ? AND ut.todo_id = t.todo_id
        )
    ";

    $stmt = $mysqli->prepare($insert_sql);
    $stmt->bind_param("ssi", $user_id, $sch_num, $user_id);
    $stmt->execute();
    $stmt->close();

    // 2. 刪除：user_todos 中該校系的 todo，但 todos 表已經不存在了
    $delete_sql = "
        DELETE ut FROM user_todos ut
        LEFT JOIN todos t ON ut.todo_id = t.todo_id
        WHERE ut.user_id = ?
        AND t.todo_id IS NULL
    ";

    $stmt = $mysqli->prepare($delete_sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
}
